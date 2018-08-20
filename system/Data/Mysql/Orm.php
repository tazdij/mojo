<?php
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
 * 			// ->and('company.status', DB_EQ, 2) // Constants are not supported yet
 * 			->exec();
	
*/
namespace Mojo\Data\Mysql;

use Mojo\Data\Mysql\Con;

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


class Orm
{
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
	
	
	
	
	public function __construct($con)
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
			$rs = $this->m_Statement->fetchAll(\PDO::FETCH_ASSOC);
		}
		else
		{
			$tmpError = $this->m_Statement->errorInfo();
			throw new \Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
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
			throw new \Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
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
		
		$tmpCriteria['field'] = $field;
		$tmpCriteria['operator'] = $operator;
		$tmpCriteria['value'] = $value;
		$tmpCriteria['join'] = $join;
		$tmpCriteria['ident'] = time() + rand(0, 20000);
		
		$this->m_Criteria[] = $tmpCriteria;
		
		//Return for chaining
		return $this;
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
						$set['field'] = $key;
						$set['value'] = $val;
						$set['ident'] = time() + rand(0, 20000);
                        //BUG: The ident should be saved in a lookup table
                        //  and user to garanty uniqueness
						$this->m_Set[] = $set;
					}
				}
				else
				{
					$set['field'] = $fields;
					$set['value'] = $value;
					$set['ident'] = time() + rand(0, 20000);
					
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
	
	public function exec()
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
					throw new \Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2] . "\n" . $this->m_Query);
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
					throw new \Exception('Error executing prepared SQL statement:' . "\n" . $tmpError[2]);
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
					throw new \Exception('Error executing prepared SQL statement: ' . "\n" . $tmpError[2]);
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
					throw new \Exception('Error executing prepared SQL statement: ' . "\n" . $tmpError[2]);
					$rs = false;
				}
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
		
		if ($intNumCriteria > 0)
		{
			foreach ($this->m_Criteria as &$tmpCriteria)
			{
				//Build the List of fields and Variable names for Prepared statements
				$this->m_Statement->bindParam(':sel_' . $tmpCriteria['ident'], $tmpCriteria['value']);
			}
		}
	}
	
	private function _pushSetValues()
	{
		foreach ($this->m_Set as &$set)
		{
			$this->m_Statement->bindParam(':upd_' . $set['ident'], $set['value']);
		}
	}
	
	private function _pushInsertValues()
	{
		foreach ($this->m_Set as &$set)
		{
			$this->m_Statement->bindParam(':ins_' . $set['ident'], $set['value']);
		}
	}

}