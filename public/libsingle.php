<?php

class SingleDB {

	private static $_inst = NULL;

	public static function getInstance() {
		if (static::$_inst == NULL) {
			static::$_inst = new static();
		}

		return static::$_inst;
	}



	/**
	 * As normal class
	 */

	public $connection;


	public function connect() {
		if ($this->connection == NULL) {
			$this->connection = new mysqli('localhost', 'dbadmin', 'MY_GamB1t', 'affiliatenet');
		}

		return $this->connection;
	}




}