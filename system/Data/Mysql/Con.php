<?php

namespace Mojo\Data\Mysql;

use Mojo\System\Config;

class Con
{
	public $strHost;
	public $strPort;
	public $strUser;
	public $strPass;
	public $strDatabase;
	public $strCharset;
	public $strTablePrefix;
	
	public $con = NULL;



	/* Removed Singleton Pattern, in Connection object itself.
	    The instance management is in the SQLDB static class, Allowing a single
	    install/application to hold connections to different databases at the
	    same time. Or, multiple connections to a single database.

	public static $inst = NULL;
	public static function get() {
		if (self::$inst == NULL) {
			// Get the configuration from app
			Config::load('database');
			$conf = Config::getAll('database');
			//print_r($conf);
			
			self::$inst = new Con($conf['host'], $conf['user'], $conf['pass'], $conf['database'], $conf['charset'], $conf['prefix']);
		}

		return self::$inst;
	}
	*/
	
	public function __construct($strHost, $strPort, $strUser, $strPass, $strDatabase, $strCharset='utf8mb4', $strTablePrefix='') {
		$this->strHost = $strHost;
		$this->strPort = $strPort;
        $this->strUser = $strUser;
        $this->strPass = $strPass;
		$this->strDatabase = $strDatabase;
		$this->strCharset = $strCharset;
        $this->strTablePrefix = $strTablePrefix;
        try {
			$strCon = "mysql:host={$strHost};port={$strPort};dbname={$strDatabase};charset={$strCharset}";
			$this->con = new \PDO($strCon,  $strUser, $strPass);
		} catch (\PDOException $e) {
			throw new \Exception("Error!: " . $e->getMessage());
		}
	}
	
	public function beginTransaction() {
		return $this->con->beginTransaction();
	}
	
	public function rollBackTransaction() {
		return $this->con->rollBack();
	}
	
	public function commitTransaction() {
		return $this->con->commit();
	}
}
