<?php
// header("Content-Type: application/json; charset=UTF-8");
// if (!isset($_SESSION["auth_token"]) || $_SESSION["auth_token"] !== true) {
// 	echo json_encode(array("error" => "Authentication token is required"));
// 	return;
// }
require_once __DIR__.'/../config/config.php';
class Database {
	private $host;
	private $username;
	private $password;
	protected $dbname;
	protected $conn;

	protected function __construct($dbname = "test") {
		$this->host     = DB_HOST;
		$this->username = DB_USERNAME;
		$this->password = DB_PASSWORD;
		$this->dbname   = $dbname;

		try {
			$this->conn = new PDO("pgsql:host=$this->host;dbname=$this->dbname;", $this->username, $this->password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
		} catch (PDOException $error) {
			return array('error' => $error->getMessage());
		}
	}
	protected function conn() {
		return $this->conn;
	}

}