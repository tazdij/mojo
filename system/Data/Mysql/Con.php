<?php

namespace Mojo\Data\Mysql;



class Con
{
	public $strHost;
	public $strUser;
	public $strPass;
	public $strDatabase;
	public $strTablePrefix;
	
	public $con;
	
	public function __construct($strHost, $strUser, $strPass, $strDatabase, $strTablePrefix='')
	{
		$this->strHost = $strHost;
        $this->strUser = $strUser;
        $this->strPass = $strPass;
        $this->strDatabase = $strDatabase;
        $this->strTablePrefix = $strTablePrefix;
        try
		{
			$this->con = new \PDO('mysql:host=' . $strHost . ';dbname=' . $strDatabase . ';charset=utf8',  $strUser, $strPass);
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
