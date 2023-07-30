<?php

namespace Mojo\Data\Mysql;

use \PDO;
use \Exception;



/*
 * TDBQuery.class
 * 	Developed By: Donald Duvall (DEDuvall, VallTek)
 * 	Date: 2012-09-01
 * 
 * Description:
 * TDBQuery is a chaining query object which allows for simple query
 * building directly from code. This includes Insert, Update, Delete, & Select
 * statements. There is also the ability to join on queries (currently only
 * for Select statements) This should simplify the PHP code for database
 * access. It uses prepared statements and PDO to secure all data for no SQL
 * Injections.
 * 
 * Example Usage:
 * 
 * DIRECT QUERY:
 * $rs = $qry->query('SELECT * FROM `table` WHERE `rec_id` = ?', [$rec_id]);
 * 
 * SELECT:
 * $rs = $qry->select(array('name', 'lastname', 'desk_phone' => 'AsPhoneField'))
 * 		->from('contacts')
 * 		->where('start_date', DB_LT | DB_EQ, date('Y-m-d', time() - 60*60*24*30))
 * 		->limit(10)
 * 		->orderBy('name', DB_ASC)
 * 		->exec();
 * 
 * UPDATE:
 * $qry->update('contacts')
 * 		->set(array('lastname' => 'Jones'))
 * 		->where('contactid', DB_EQ, 15)
 * 		->exec();
 * 
 * INSERT:
 * $qry->insert('contacts') //implied from
 * 		->set(array('name' => 'Donald', 'lastname' => 'Duvall', 'email' => 'don.duvall@deduvall.com'))
 * 		->exec();
 * 
 * DELETE:
 * $qry->delete('contacts') //implied from
 * 		->where('county', DB_EQ, 'kern')
 * 		->exec();
 * 
 * SELECT WITH JOIN: 
 * $rs = $qry->select(array('contact.name' => 'ContactName', 
 * 							'contact.lastname' => 'ContactLastName', 
 * 							'contact.phone' => 'ContactPhone', 
 * 							'company.name' => 'CompanyName', 
 * 							'company.phone' => 'CompanyPhone'))
 * 			->from('contacts')
 * 			->innerJoin('company')->on('company.companyID', DB_EQ, 'contacts.companyID')
 * 			->and('company.status', DB_EQ, 2) // Constants are not supported yet
 * 			->exec();
	
PLANNED IMPROVEMENTS:
  
  --------------------------------------------------------------
  IMPROVE RESULT FIELD MUTABILITY via SQL_Inject
  Queries are currently very limited on their field result customizations, for example
  $res = $this->db->select([
	  			'rec_id', 
				'app_id', 
				'app_title', 
				'app_description'])->from('apps')
            ->where('app_id', DB_EQ, DB::Bin($app_id_bin))
            ->exec();
	
  IMPROVED for mutating data as it is stream out
  $res = $this->db->select([
	  			'rec_id', 
				DB::Inject('HEX', 'app_id'),				// implicit rename to field name?
				'app_title', 
				'app_description'])->from('apps')
            ->where('app_id', DB_EQ, DB::Bin($app_id_bin))
            ->exec();

  Idealy this will produce something like:
  SELECT
    `rec_id`,
	HEX(`app_id`) AS `app_id`,
	`app_title`,
	`app_description`
  FROM `apps`
  WHERE `app_id` = :sel_app_id1

  This prepared statement would then have the binary app_id bound when executed, as normal.

  *******************
  * BONUS, along with this change, I would like to add the DB::Inject feature into Criteria.
  this will likely give us even more options, and solutions during edge cases. 

  $res = $this->db->select([
	  			'rec_id', 
				DB::Inject('HEX', 'app_id'),				// implicit rename to field name?
				'app_title', 
				'app_description'])->from('apps')
            ->where('app_id', DB_EQ, DB::Inject('UNHEX', $app_id_hex))
            ->exec();

  ---------------------------------------------------------------


*/


//Define Operators
define('DB_LT', 1);
define('DB_GT', 2);
define('DB_EQ', 4);
define('DB_IN', 8);
define('DB_LIKE', 16);
define('DB_OR', 32);
define('DB_AND', 64);

define('DB_ALL', '*');

//Define Query Types
define('DB_SELECT', 1);
define('DB_UPDATE', 2);
define('DB_INSERT', 4);
define('DB_DELETE', 8);

//Define QueryChain Modes
define('DB_NONE', 1);
define('DB_WHERE', 2);
define('DB_JOIN', 4);

//Define Join Types
define('DB_LEFTJOIN', 1);
define('DB_INNERJOIN', 2);
define('DB_RIGHTJOIN', 4);
define('DB_CROSSJOIN', 8);


//Define OrderBy Directions
define('DB_ASC', 'ASC');
define('DB_DESC', 'DESC');

//Define Result Types
define('DB_RS', 1);
define('DB_ROW', 2);
define('DB_VAR', 3);
//define('DB_NONE', 4);


//TODO: 


/**
 * A simple class to allow the builder to know that the code, wants to insert a SQL Call into the
 * generated sql statement, possibly Wrapping a value or Column.
 */
class DB_SQL_Inject {
	public $_sql_function;
	public $_params = [];
	
	public function __construct($func, $params=[]) {

		// The string name of the MySQL function
		$this->_sql_function = (string)$func;

		// An array of Values, or Additional SQL_Inject objects. (allowing nesting)
		$this->_params = $params;
	}

	public function renderSql() {
		$sql = $this->_sql_function . '(' . ' ';

		// loop adding args, and nested SQL_Injects
		$arg_str = '';
		foreach ($this->_params as $param) {
			if ($param instanceof DB_SQL_Inject) {
				// This is a nested call
				$arg_str .= $param->renderSql();
			} else {
				if     (is_numeric($param)) {
					$arg_str .= (string)$param;
				} 
				elseif (is_null($param)) {
					$arg_str .= 'NULL';
				}
				elseif ($param === TRUE) {
					$arg_str .= 'TRUE';
				}
				elseif ($param === FALSE) {
					$arg_str .= 'FALSE';
				}
				else {
					$arg_str .= "'" . (string)$param . "'";
				}
			}

			$arg_str .= ', ';
		}
		$sql .= substr($arg_str, 0, -2);

		$sql .= ' )';

		return $sql;
	}
}


// Some Helper constants for MYSQL Injections into the QueryBuilder methods
// maybe global functions, instead?

// These functions will trigger the builder to inject the appropriate SQL syntax to run these
// commands on the server
//function db_bin_uuid() {
//	
//}
// These helper functions should be public static on DB::

/**
 * This class is used to ensure that the PDO and QueryBuilder, will use the correct
 * driver methods for this specific value.
 * 
 * NOTE: 
 *   Do not instantiate directly. Only via the DB:: static function helpers. The underlying
 *   structure of this class and how it is interpreted will likely
 *
 * 
 * USAGE: (public usage example, see DB Docs for more details)
 *    //
 *    $qry->insert('mytable')->set('first_name', DB::String('Don'))->exec();
 */
class DB_Typed_Value {
	const TYPE_NULL = 0;
	const TYPE_STR  = 1;
	const TYPE_INT  = 2;
	const TYPE_BIN  = 3;
	const TYPE_BOOL = 4;
	const TYPE_STR_NATL = 5;
	const TYPE_STR_CHAR = 6;

	public $_type;
	public $_value;

	public function __construct($type, $value) { 
		$this->_type = $type;
		$this->_value = $value;
	}

	public function GetValue() {
		return $this->_value;
	}

	public function PDOParam() {
		switch ($this->_type) {
			case self::TYPE_BIN:
				return PDO::PARAM_LOB;
			case self::TYPE_NULL:
				return PDO::PARAM_NULL;
			case self::TYPE_BOOL:
				return PDO::PARAM_BOOL;
			case self::TYPE_INT:
				return PDO::PARAM_INT;
			case self::TYPE_STR:
				return PDO::PARAM_STR;
			case self::TYPE_STR_CHAR:
				return PDO::PARAM_STR_CHAR;
			case self::TYPE_STR_NATL:
				return PDO::PARAM_STR_NATL;
		}
	}
}



class Orm {

	public static function GenerateUUIDHex() {
		return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),
	
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,
	
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,
	
			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}


	/*****************************************************************
	 * STATIC UTILITY/CONVENIENCE METHODS
	 * To make a natural namespace like feel to the library's more
	 * advanced features.
	 ****************************************************************/

	public static function Str($val) {
		return new DB_Typed_Value(DB_Typed_Value::TYPE_STR, (string)$val);
	}

	public static function Bin($val) {
		return new DB_Typed_Value(DB_Typed_Value::TYPE_BIN, $val);
	}

	public static function Int($val) {
		return new DB_Typed_Value(DB_Typed_Value::TYPE_INT, $val);
	}

	public static function Bool($val) {
		return new DB_Typed_Value(DB_Typed_Value::TYPE_BOOL, (bool)$val);
	}

	
	public static function Func($func, $vals = []) {
		return new DB_SQL_Inject($func, $vals);
	}

	public static function Guid() {
		return new DB_Typed_Value(DB_Typed_Value::TYPE_BIN, hex2bin(self::GenerateUUIDHex()));
	}




	private $m_DBCon;
	
	public $m_Query = null;
	private $m_QueryType = null;
	
	/* m_Fields
	 * Used specifically for the Select Statements Return values
	 * 
	 * key => value
	 * The above format is used to apply aliases in the SQL
	 * 
	 * if there is a key and value present:
	 * key = db field name
	 * value = alias name
	 * else
	 * value = db field name
	*/
	private $m_Fields = array();
	
	/* m_Criteria
	 * Used for the WHERE cluase in any statement.
	*/
	private $m_Criteria = array();
	private $m_OrderBy = array();
	private $m_Limit = null;
	private $m_Set = array();
	private $m_Table = null;
	private $m_ChainMode = DB_NONE;

	/* m_NextFieldIdent
	 * A variable the increments, and gives the next number to add to query
	 * builder set structure.
	 * NOTE: the old implementation used a random(20000)
	*/
	private $m_NextFieldIdent = 1;

	// Performance Improvement for interative operations
	//TODO: Add a Query Hash Check, and Cache Prepared Statements
	//	It might be worthwhile to generate a SQL statement checksum or hash
	//  and compare it to our current QueryBuilder STATE, and if matched
	//  skip to the bind, and reuse our previously prepared statement...
	//  EXEC SHOULD MAKE THIS OPTIONAL, VIA FLAG
	
	/* m_Join
	 * This is not exactly decided how it will work yet
	 * 
	 * [0 => [
	 * 		"Table" => ["company", "comp"],
	 * 		"Type" => 
	 * 		"Criteria" => [
	 * 				0 => ['field' => 'comp.companyID', 'operator' => DB_EQ, 'value' => 'contacts.companyID']
	 * 			]
	 * 		]
	 * ]
	 * 
	 * Criteria Value Logic: it is a SQL Field if (IsString && !Contains("'"))
	 * 
	*/
	private $m_Joins = array();
	
	/* m_Statement
	 * This is the actual PDO Prepared Statment Object
	 * it is where variables values are bound to, and after an
	 * insert it contains the insert_id value
	*/
	private $m_Statement = null;
	
	
	// -------------------------------------------------------
	// Direct Query functionality
	// -------------------------------------------------------
	//private $_stmt;
	
	
	
	
	public function __construct(Con $con)
	{
		// Get database configurations
		
		$this->m_DBCon = $con;
		
	}
	
	// -------------------------------------------------------
	// Connection Transaction, shortcuts
	// -------------------------------------------------------
	public function beginTran()
	{
		$this->m_DBCon->beginTransaction();
	}
	
	public function commitTran()
	{
		$this->m_DBCon->commitTransaction();
	}
	
	public function rollbackTran()
	{
		$this->m_DBCon->rollBackTransaction();
	}
	
	
	// -------------------------------------------------------
	// Direct Query functionality
	// -------------------------------------------------------
	public function query($sql, $params=array())
	{
		$this->m_Statement = $this->m_DBCon->con->prepare($sql);
		
		$rs = true;
		
		if ($this->m_Statement->execute($params))
		{
			$rs = $this->m_Statement->fetchAll(PDO::FETCH_ASSOC);
		}
		else
		{
			$tmpError = $this->m_Statement->errorInfo();
			throw new Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
			$rs = false;
		}
		
		return $rs;
	}
	
	// Used for a query execute returning true or false;
	public function execute($sql, $params=array())
	{
		$this->m_Statement = $this->m_DBCon->con->prepare($sql);
		
		$rs = true;
		if ($this->m_Statement->execute($params))
		{
			$rs = true;
		}
		else
		{
			$tmpError = $this->m_Statement->errorInfo();
			throw new Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
			$rs = false;
		}
		
		return $rs;
	}
	
	public function lastInsertId()
	{
		return $this->m_DBCon->con->lastInsertId();
	}

	
	/****************************************************
	*	select()
	*		Sets the field or fields and the type for the
	*		Query when executing
	****************************************************/
	public function select($fields)
	{
		//First clear all of the temporary variables
		$this->_reset();
		
		$this->m_QueryType = DB_SELECT;
		
		if (is_array($fields))
		{
			foreach ($fields as $key => $val)
			{
				if (is_string($key))
				{
					$this->m_Fields[] = array($key => $val);
				}
				else
				{
					$this->m_Fields[] = $val;
				}
			}
		}
		else
		{
			$this->m_Fields[] = $fields;
		}
		
		//Return for chaining
		return $this;
	}
	
	/****************************************************
	*	update()
	*		Sets the type for the Query when executing, 
	*		Fields and Values are in the set()
	****************************************************/
	public function update($table)
	{
		$this->_reset();
		
		$this->m_QueryType = DB_UPDATE;
		
		$this->m_Table = $table;
		
		return $this;
	}
	
	/****************************************************
	*	insert()
	*		Sets the type for the Query when executing, 
	*		Fields and Values are in the set()
	****************************************************/
	public function insert($table)
	{
		$this->_reset();
		$this->m_QueryType = DB_INSERT;
		
		$this->m_Table = $table;
		
		return $this;
	}
	
	/****************************************************
	*	delete()
	*		Sets the type for the Query when executing, 
	*		Fields and Values are in the set()
	****************************************************/
	public function delete($table)
	{
		$this->_reset();
		$this->m_QueryType = DB_DELETE;
		
		$this->m_Table = $table;		
		
		return $this;
	}
	
	//Aditional Commands
	//	from, where, orderBy, limit, and, or
	//	Later add join and on functions
	public function from($table)
	{
		$this->m_Table = $table;
		
		//Return for chaining
		return $this;
	}
	
	public function where($field, $operator, $value, $join=DB_AND)
	{
		//Set the ChainMode
		//	This allows others functions to know what they are chaining to for
		//	Logic to decide what to do with the arguments.
		$this->m_ChainMode = DB_WHERE;

		$val_data = $this->_analyseValue($value);
		
		$tmpCriteria['field'] = $field;
		$tmpCriteria['operator'] = $operator;
		$tmpCriteria['value'] = $val_data['val'];
		$tmpCriteria['join'] = $join;
		$tmpCriteria['ident'] = $field . '_' . $this->_nextFieldId();
		$tmpCriteria['type'] = $val_data['type'];
		
		$this->m_Criteria[] = $tmpCriteria;
		
		//Return for chaining
		return $this;
	}
	
	public function orWhere($field, $operator, $value) {
		return $this->where($field, $operator, $value, DB_OR);
	}
	
	public function orderBy($field, $order=DB_ASC)
	{
		//Make sure no mare chaining mode is selected
		$this->m_ChainMode = DB_NONE;
		
		$tmpOrder['field'] = $field;
		$tmpOrder['order'] = $order;
		
		$this->m_OrderBy[] = $tmpOrder;
		
		//Return for chaining
		return $this;
	}
	
	public function limit($start, $count=null)
	{
		//Make sure no more chaining mode is selected
		$this->m_ChainMode = DB_NONE;
		
		$this->m_Limit['start'] = $start;
		if ($count != null)
			$this->m_Limit['count'] = $count;
			
		//Return for chaining
		return $this;
	}

	
	public function set($fields, $value=null)
	{
		//TODO: set for update function
		//	Array of 'field' => value
		switch ($this->m_QueryType)
		{
			case DB_INSERT:
				
			case DB_UPDATE:
				if (is_array($fields))
				{
					foreach ($fields as $key => $val)
					{
						$val_info = $this->_analyseValue($val);
						$set['field'] = $key;
						$set['value'] = $val_info['val'];
						$set['ident'] = $key . '_' . $this->_nextFieldId();
						$set['type'] = $val_info['type'];
						
						$this->m_Set[] = $set;
					}
				}
				else
				{
					$val_info = $this->_analyseValue($value);
					$set['field'] = $fields;
					$set['value'] = $val_info['val'];
					$set['ident'] = $fields . '_' . $this->_nextFieldId();
					$set['type'] = $val_info['type'];
					
					$this->m_Set[] = $set;
				}
				break;
			
			default:
				throw new Exception('set is not allowed in current query');
				break;
		}
		
		//Return for chaining
		return $this;
	}

	/**
	 * Joining functions
	 */
	 
	public function join($table, $type=DB_LEFTJOIN) {
		//TODO: add to join
		$this->m_ChainMode = DB_JOIN;
		
		$this->m_Joins[] = array('table' => $table, 'type' => $type, 'criteria' => array());
		
		return $this;
	}
	 
	public function innerJoin($table)
	{
		return $this->join($table, DB_INNERJOIN);
	}
	
	public function leftJoin($table) {
		return $this->join($table, DB_LEFTJOIN);
	}
	
	public function rightJoin($table) {
		return $this->join($table, DB_RIGHTJOIN);
	}
	
	public function crossJoin($table) {
		return $this->join($table, DB_CROSSJOIN);
	}
	
	public function on($field, $operator, $value, $join=DB_AND)
	{
		//TODO: Check chain mode, and if there is join element
		
		$this->m_Joins[count($this->m_Joins) - 1]['criteria'][] = array('field' => $field, 'operator' => $operator, 'value' => $value, 'join' => $join);
		
		//Return for chaining
		return $this;
	}
	
	public function andOn($field, $operator, $value)
	{
		return $this->on($field, $operator, $value, DB_AND);
	}
	
	public function orOn($field, $operator, $value)
	{
		return $this->on($field, $operator, $value, DB_OR);
	}
	
	public function exec($result_type=DB_RS)
	{
		$rs = null;
		
		switch ($this->m_QueryType)
		{
			case DB_SELECT:
				$this->m_Query = $this->_buildSelect();
				$this->m_Statement = $this->m_DBCon->con->prepare($this->m_Query);
				$this->_pushCriteriaValues();
				if ($this->m_Statement->execute())
				{
					$rs = $this->m_Statement->fetchAll(PDO::FETCH_ASSOC);
				}
				else
				{
					$tmpError = $this->m_Statement->errorInfo();
					throw new Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2] . "\n" . $this->m_Query);
					$rs = false;
				}
				break;
			
			case DB_UPDATE:
				$this->m_Query = $this->_buildUpdate();
				$this->m_Statement = $this->m_DBCon->con->prepare($this->m_Query);
				$this->_pushSetValues();
				$this->_pushCriteriaValues();
				if ($this->m_Statement->execute())
				{
					$rs = true;
				}
				else
				{
					$tmpError = $this->m_Statement->errorInfo();
					throw new Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
					$rs = false;
				}
				break;
				
			case DB_INSERT:
				$this->m_Query = $this->_buildInsert();
				$this->m_Statement = $this->m_DBCon->con->prepare($this->m_Query);
				$this->_pushInsertValues();
				if ($this->m_Statement->execute())
				{
					$rs = $this->lastInsertId();
				}
				else
				{
					$tmpError = $this->m_Statement->errorInfo();
					throw new Exception('Error executing prepared SQL statement: ' . "\n" . $tmpError[2]);
					$rs = false;
				}
				break;
				
			case DB_DELETE:
				$this->m_Query = $this->_buildDelete();
				$this->m_Statement = $this->m_DBCon->con->prepare($this->m_Query);
				$this->_pushCriteriaValues();
				if ($this->m_Statement->execute())
				{
					$rs = true;
				}
				else
				{
					$tmpError = $this->m_Statement->errorInfo();
					throw new Exception('Error executing prepared SQL statement: ' . "\n" . $tmpError[2]);
					$rs = false;
				}
				break;
		}
		
		switch ($result_type) {
			case DB_RS:
				// Do nothing
				break;
				
			case DB_ROW:
				if (count($rs) > 0)
					return $rs[0];
				else
					return false;
				break;
				
			case DB_VAR:
				if (count($rs) > 0)
					$row = $rs[0];
				else
					return null;
				
				// return the first array element value
				foreach ($row as $val) {
					return $val;
				}
				break;
				
			default:
				throw new Exception('Error ResultType not recognized');
				break;
		}
		
		return $rs;
	}
	
	
	
	
	/**************************************************
		PRIVATE FUNCTIONS
	**************************************************/
	private function _reset()
	{
		$this->m_Query = null;
		$this->m_QueryType = null;
		$this->m_Fields = array();
		$this->m_Criteria = array();
		$this->m_OrderBy = array();
		$this->m_Limit = null;
		$this->m_Set = array();
		$this->m_Table = null;
		$this->m_Joins = array();
		$this->m_ChainMode = DB_NONE;
		$this->m_Statement = null;
		$this->m_NextFieldIdent = 1;
	}

	protected function _nextFieldId() {
		return $this->m_NextFieldIdent++;
	}
	
/**
	 * _analyseValue looks at a value of any type, and returns the type and actual (native) value
	 * as an array with 'type', 'val' elements by name.
	 */
	private function _analyseValue($value) {
		$res = ['type' => PDO::PARAM_STR, 'val' => $value];

		// Default type info (usually a string)
		
		// Get the value information 
		if ($value instanceof DB_Typed_Value) {
			$res['type'] = $value->PDOParam();
			$res['val'] = $value->GetValue();
		} else {
			// Not a Typed_Value, try to guess the correct type
			if (is_null($value)) {
				$res['type'] = PDO::PARAM_NULL;
			} 
			elseif (is_int($value)) {
				$res['type'] = PDO::PARAM_INT;
			}
			elseif (is_bool($value)) {
				$res['type'] = PDO::PARAM_BOOL;
			}
		}
		
		return $res;
	}

	private function _buildFields()
	{
		$strFields = '';
		
		if (count($this->m_Fields) > 0)
		{
			$i = 0;
			foreach ($this->m_Fields as $field)
			{
				if (is_array($field))
				{
					//Get the key and value
					foreach ($field as $key => $val)
					{
						$strFields .= '`' . str_replace('.', '`.`', $key) . '` AS `' . $val . '`';
					}
				}
				else
				{
					if (strpos($field, DB_ALL, 0) > -1)
						$strFields .= str_replace('.', '.', $field) . '';
					else
						$strFields .= '`' . str_replace('.', '`.`', $field) . '`';
				}
				
				if ($i < count($this->m_Fields)-1)
				{
					$strFields .= ', ';
				}
				$i++;
			}
		}
		else
		{
			throw new Exception('No fields are set for return on select statement');
		}
		
		return $strFields;
	}
	
	private function _buildFrom()
	{
		
		//TODO: Add join inside from statement
		
		$strResult = '';
		if (isset($this->m_Table))
		{
			if (is_array($this->m_Table))
			{
				foreach ($this->m_Table as $key => $val)
					$strResult = 'FROM `' . $key . '` AS `' . $val . '` ';
			}
			else
			{
				$strResult = 'FROM `' . $this->m_Table . '` ';
			}
		}
		else
		{
			throw new Exception('No table is set for the select statement');
		}
		
		if (is_array($this->m_Joins)) {
			foreach ($this->m_Joins as &$join) {
				// Handle Join
				switch ($join['type']) {
					case DB_INNERJOIN:
						$strResult .= 'INNER JOIN ';
						break;
					
					case DB_LEFTJOIN:
						$strResult .= 'LEFT OUTER JOIN ';
						break;
						
					case DB_RIGHTJOIN:
						$strResult .= 'RIGHT JOIN ';
						break;
						
					case DB_CROSSJOIN:
						$strResult .= 'CROSS JOIN ';
						break;
				}
				
				// Add Table (Allowing for alias)
				if (is_array($join['table'])) {
					foreach ($join['table'] as $key => $val)
						$strResult .= '`' . $key . '` AS `' . $val . '` ';
				} else {
					$strResult .= '`' . $join['table'] . '` ';
				}
				
				
				// Handle each criteria statement
				if (is_array($join['criteria']))
					$strResult .= 'ON ';
				
				$i = 0;	
				foreach ($join['criteria'] as &$criteria) {
					$con_type = '';
					if ($criteria['join'] & DB_AND) { $con_type = 'AND'; }
					else if ($criteria['join'] & DB_OR) { $con_type = 'OR'; }
					
					if ($i > 0)
						$strResult .= ' ' . $con_type . ' ';
					
					$operator = '';
					if ($criteria['operator'] & DB_LT) { $operator .= '<'; }
					if ($criteria['operator'] & DB_GT) { $operator .= '>'; }
					if ($criteria['operator'] & DB_EQ) { $operator .= '='; }
					if ($criteria['operator'] & DB_IN) { $operator .= 'IN'; }
					if ($criteria['operator'] & DB_LIKE) { $operator .= 'LIKE'; }
					
					$strResult .= '`' . str_replace('.', '`.`', $criteria['field']) . '` ' . $operator . ' `' . str_replace('.', '`.`', $criteria['value']) . '`';
					
					$i++; 
				}
			}
		} // End if Joins
		
		return trim($strResult);
	}
	
	private function _buildWhere()
	{
		$strResult = '';
		
		if (count($this->m_Criteria) > 0)
		{
			$strResult = 'WHERE ';
			
			$i = 0;
			foreach ($this->m_Criteria as $criteria)
			{
				$join = '';
				if ($criteria['join'] & DB_AND) { $join = 'AND'; }
				elseif ($criteria['join'] & DB_OR) { $join = 'OR'; }
				
				if ($i > 0)
					$strResult .= ' ' . $join . ' ';
			
				//Get the operator string value
				$operator = '';
				if ($criteria['operator'] & DB_LT) { $operator .= '<'; }
				if ($criteria['operator'] & DB_GT) { $operator .= '>'; }
				if ($criteria['operator'] & DB_EQ) { $operator .= '='; }
				if ($criteria['operator'] & DB_IN) { $operator .= 'IN'; }
				if ($criteria['operator'] & DB_LIKE) { $operator .= 'LIKE'; }
				
				$strResult .= '`' . str_replace('.', '`.`', $criteria['field']) . '` ' . $operator . ' :sel_' . $criteria['ident'];
				
				$i++;
			}
		}
		
		return $strResult;
	}
	
	private function _buildOrderBy()
	{
		$strResult = '';
		
		if (count($this->m_OrderBy) > 0)
			$strResult .= 'ORDER BY ';
			
		$i = 0;
		foreach ($this->m_OrderBy as &$orderby)
		{
			if ($i > 0)
				$strResult .= ', ';
				
			$strResult .= '`' . str_replace('.', '`.`', $orderby['field']) . '`';
			$strResult .= ' ' . $orderby['order'];
			
			$i++;
		}
		
		return $strResult;
	}
	
	private function _buildLimit()
	{
		if (is_null($this->m_Limit))
			return '';
			
		$strResult = 'LIMIT ';
		if (!isset($this->m_Limit['count']))
		{
			$strResult .= $this->m_Limit['start'];
		}
		else
		{
			$strResult .= $this->m_Limit['start'] . ', ' . $this->m_Limit['count'];
		}
		
		return $strResult;
	}
	
	private function _buildSet()
	{
		$strResult = 'SET ';
		
		$i = 0;
		foreach ($this->m_Set as &$field)
		{
			if ($i > 0)
				$strResult .= ', ';
				
			$strResult .= '`' . str_replace('.', '`.`', $field['field']) . '` = :upd_' . $field['ident'];
			
			$i++;
		}
		
		return $strResult;
	}
	
	private function _buildInsertFields()
	{
		$strResult = '(';
		
		$i = 0;
		foreach ($this->m_Set as &$field)
		{
			if ($i > 0)
				$strResult .= ', ';
			
			$strResult .= '`' . str_replace('.', '`.`', $field['field']) . '`';
			
			$i++;
		}
		
		$strResult .= ')';
		
		return $strResult;
	}
	
	/*	_buildSetValues
	 * used by the Insert command exec to build the VALUES (...) portion of the query
	 * naming the variables with unique identities
	 */
	private function _buildSetValues()
	{
		$strResult = 'VALUES (';
		
		$i = 0;
		foreach ($this->m_Set as &$field)
		{
			if ($i > 0)
				$strResult .= ', ';
				
			$strResult .= ':ins_' . $field['ident'];
			
			$i++;
		}
		
		$strResult .= ')';
		
		return $strResult;
	}
	
	private function _buildSelect()
	{
		$strResult = 'SELECT ';
		$strResult .= $this->_buildFields() . ' ';
		$strResult .= $this->_buildFrom() . ' ';
		$strResult .= $this->_buildWhere() . ' ';
		$strResult .= $this->_buildOrderBy() . ' ';
		$strResult .= $this->_buildLimit() . ' ';

		//print($strResult);
		
		return $strResult;
	}
	
	private function _buildUpdate()
	{
		$strResult = 'UPDATE `' . str_replace('.', '`.`', $this->m_Table) . '` ';
		$strResult .= $this->_buildSet() . ' ';
		$strResult .= $this->_buildWhere() . ' ';
		$strResult .= $this->_buildOrderBy() . ' ';
		$strResult .= $this->_buildLimit();
		
		return $strResult;
	}
	
	private function _buildInsert()
	{
		$strResult = 'INSERT INTO `'. $this->m_Table . '` ';
		$strResult .= $this->_buildInsertFields() . ' ';
		$strResult .= $this->_buildSetValues() . ' ';
				
		//print($strResult);

		return $strResult;
	}
	
	private function _buildDelete()
	{
		$strResult = 'DELETE FROM `' . $this->m_Table . '` ';
		$strResult .= $this->_buildWhere() . ' ';
		
		return $strResult;
	}
	
	/*******************************************
	* Push values to the Prepared Statement buffer
	*
	*******************************************/
	private function _pushCriteriaValues()
	{
		//loop each field in the array and place in the statement the variables
		$intNumCriteria = count($this->m_Criteria);
		
		//print_r($this->m_Criteria);

		if ($intNumCriteria > 0)
		{
			foreach ($this->m_Criteria as &$tmpCriteria)
			{
				//Build the List of fields and Variable names for Prepared statements
				$this->m_Statement->bindParam(':sel_' . $tmpCriteria['ident'], $tmpCriteria['value'], $tmpCriteria['type']);
			}
		}
	}
	
	private function _pushSetValues()
	{
		//var_dump($this->m_Set);
		foreach ($this->m_Set as &$set)
		{
			$this->m_Statement->bindParam(':upd_' . $set['ident'], $set['value'], $set['type']);
		}
	}
	
	private function _pushInsertValues()
	{
		//var_dump($this->m_Set);
		foreach ($this->m_Set as &$set)
		{
			$this->m_Statement->bindParam(':ins_' . $set['ident'], $set['value'], $set['type']);
		}
	}

}