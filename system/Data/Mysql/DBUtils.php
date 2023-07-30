<?php
namespace Mojo\Data\Mysql;

class DBUtils {
	private static $con = null;

	public static function Connect($hostname, $port, $user, $pass, $database, $charset) {
		self::$con = new DBCon($hostname, $port, $user, $pass, $database, $charset);
	}
	
	public static function &getCon() {
		if (self::$con !== null)
			return self::$con;
		
		return null;
	}
	
	public static function newQry() {
		if (self::$con !== null)
			return new DB(self::$con);
		
		return null;
	}
}
