<?php

namespace Mojo\Data\Mysql;

use \PDO;
use \PDOException;
use \Exception;

class DBCon
{
    public $strHost;
    public $strPort;
	public $strUser;
	public $strPass;
	public $strDatabase;
	public $strCharset;
	
	public $con;
	
	public function __construct($strHost, $strPort, $strUser, $strPass, $strDatabase, $strCharset='utf8mb4')
	{
        $this->strHost = $strHost;
        $this->strPort = $strPort;
        $this->strUser = $strUser;
        $this->strPass = $strPass;
        $this->strDatabase = $strDatabase;
        $this->strCharset = $strCharset;
        try
		{
			$this->con = new PDO('mysql:host=' . $strHost . ';port=' . $strPort . ';dbname=' . $strDatabase . ';charset=' . $strCharset,  $strUser, $strPass);
		}
		catch (PDOException $e)
		{
			throw new Exception("Error!: " . $e->getMessage());
		}
	}
	
	public function beginTransaction()
	{
		return $this->con->beginTransaction();
	}
	
	public function rollBackTransaction()
	{
		return $this->con->rollBack();
	}
	
	public function commitTransaction()
	{
		return $this->con->commit();
	}
}