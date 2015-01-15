<?php namespace Us\Crawler\Storage;
use \PDO;

class Database
{
	public $db = null;
	private $_db_username = '';
	private $_db_password = '';

	function __construct($db_username, $db_password = '')
	{
		$this->_db_username = $db_username;
		$this->_db_password = $db_password;
		$this->openPDO();
	}

	private function openPDO()
	{
		// connection info for PDO
		define("DB_TYPE", "mysql");
		define("DB_HOST", "127.0.0.1");
		define("DB_NAME", "ptt_crawler");
		define("DB_USER", $this->_db_username);
		define("DB_PASS", $this->_db_password);

		// error code of PDO
		define("SERVER_SHUTDOWN_CODE", "1053");

		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
		try {
			$this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
			$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			exit("Database connection error\n");
		}
	}

	public function reconnectPDO()
	{
		$this->db = null;
		$this->openPDO();
	}

	public function __destruct()
	{
		$this->db = null;
	}
}
