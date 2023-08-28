<?php
// header("Content-Type: application/json; charset=UTF-8");
// if (!isset($_SESSION["auth_token"]) || $_SESSION["auth_token"] !== true) {
// 	echo json_encode(array("error" => "Authentication token is required"));
// 	return;
// }
// require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// var_dump(JWT::class);

require_once "model_helper.php";
// require_once "jwt.php";
class validated extends Database {
	private $main_table = "tb_users";
	private $cols = ["id" => "id", "name" => "name", "password" => "password"];
	private $debug;
	private $req_payload;
	// private $access = "pass_auth_users";
	// private $access = 1;
	function __construct() {
		parent::__construct("postgres");
	}
	private function validateToken(): array {
		$res   = [];
		// $debug = [];
		try {
			$info = $this->getToken();
			// $debug["getToken"]["res"]     = $info;
			// $debug["getToken"]["res"]     = $info->getMessage();
			// $debug["getToken"]["type of"] = gettype($info);

			$this->are_info_valid($info);

			$query          = "SELECT * FROM $this->main_table WHERE " . $this->cols["id"] . " = :id";
			// $debug["query"] = $query;

			$stm = $this->conn->prepare($query);
			$stm->bindParam(":id", $info->data);
			// TODO info name por que puedo
      $stm->execute();
      
			$res["rows"] = $stm->fetchColumn();
			// $debug["rows"] = $res;

			// $res += $debug;
			return $res;
		} catch (\Throwable $th) {
			// $debug["err"] = $th->getMessage();
			// $res += $debug;

			$res["Error"] = $th->getMessage();
			return $res;
		}
	}
	private function getToken(): object {
		try {
			$hea                 = apache_request_headers();
			// $debug["hea apache"] = $hea;

			if (!isset($hea["Authorization2"]) || empty($hea["Authorization2"])) {
				throw new Exception("Seriedad por favor");
			}

			$token_arr                    = explode(" ", $hea["Authorization2"]);
			// $debug["getToken"]["explode"] = $token_arr;

			if (!is_array($token_arr)) {
				throw new Exception("Falta info");
			}
			if (count($token_arr) !== 2) {
				throw new Exception("Ya wey por favor");
			}
			if ($token_arr[0] !== "Baerer") {
				throw new Exception("porque buscas mi desgracia?");
			}
			$token = $token_arr[1];

			$decoded = JWT::decode($token, new Key(API_KEY, 'HS256'));
			// $decoded = JWT::decode($token, API_KEY, 'HS256');

			// $debug["getToken"]["decoded"]["data"]    = $decoded;
			// $debug["getToken"]["decoded"]["type of"] = gettype($decoded);

			// return $debug;
			// $decoded->debug = $debug;
			return $decoded;
		} catch (\Throwable $th) {
			$debug["err"] = $th->getMessage();
			return $th;
		}
	}
	private function are_info_valid($info): void {
		if (!is_object($info)) {
			throw new Exception("Porque men? detente");
		}
		if (!isset($info->data) || !$info->data) {
			throw new Exception("Falta data");
		}
	}
	public function valid(): array {
		$res   = [];
		// $debug = [];
		try {
			$vali                   = $this->validateToken();
			// $debug["validateToken"] = $vali;

			if (isset($vali["err"]) || empty($vali["rows"]) || $vali["rows"] !== 1) {
				$res["code"] = 401;
			} else {
				$res["code"] = 200;
			}
			// $res += $debug;
			return $res;
		} catch (\Throwable $th) {
			// $debug["code"] = 500;
			// $debug["err"]  = $th->getMessage();
			// $res += $debug;
			$res           = ["Error" => $th->getMessage(), "code" => 500];
			return $res;
		}

	}
	public function newToken($req_body): array {
		// $debug = [];
		$res   = [];

		// $debug["req_body"] = $req_body;

		try {
			$this->are_req_body_valid($req_body);
			$db          = $this->get_valid_user();
			$debug["db"] = $db;

			if (!$db) {
				$res["code"]  = 401;
				$res["msg"]   = "Usuario incorrecto";
				$res["debug"] = $debug;
				return $res;
			}
			if (!password_verify($req_body["password"], $db["password"])) {
				$debug["password_verify"] = password_verify($req_body["password"], $db["password"]);
				$res["code"]              = 401;
				$res["msg"]               = "Password incorrecto";
				$res["debug"]             = $debug;
				return $res;
			}
			$debug["password_verify"] = password_verify($req_body["password"], $db["password"]);

			$payload          = [
				"data" => $db["id"],
				"name" => $db["name"]
			];
			$debug["payload"] = $payload;

			$jwt          = JWT::encode($payload, API_KEY, ALGORITHM);
			$debug["jwt"] = $jwt;

			$res["debug"] = $debug;
			$res["token"] = $jwt;
			return $res;
		} catch (\Throwable $th) {
			$debug["err"] = $th;
			$res["Error"] = $th->getMessage();
			$res["code"]  = 500;
			$res["debug"] = $debug;
			return $res;
		}
	}
	private function get_valid_user() {
		$debug = [];

		$sql          = "SELECT * FROM $this->main_table WHERE " . $this->cols["name"] . " = :name AND " . $this->cols["id"] . " = :id";
		$debug["sql"] = $sql;

		$stm = $this->conn->prepare($sql);
		$stm->bindParam(":name", $this->req_payload["name"]);
		$stm->bindParam(":id", $this->req_payload["id"]);
		$stm->execute();

		$db          = $stm->fetch(PDO::FETCH_ASSOC);
		$db["debug"] = $debug;
		return $db;
	}
	private function are_req_body_valid($req_body): void {
		if (!isset($req_body) || !$req_body) {
			$this->debug["req_body"] = "is not set or is empty";
			throw new Exception("Fail request");
		}
		if (!is_array($req_body)) {
			$this->debug["req_body"] = "is not array";
			throw new Exception("Fail request");
		}
		if (!isset($req_body[$this->cols["name"]]) || !$req_body[$this->cols["name"]]) {
			$this->debug["req_body"] = "name is not set or is empty";
			throw new Exception("Fail request");
		}
		if (!isset($req_body[$this->cols["password"]]) || !$req_body[$this->cols["password"]]) {
			$this->debug["req_body"] = "password is not set or is empty";
			throw new Exception("Fail request");
		}
		$this->req_payload          = $req_body;
		$this->debug["req_payload"] = $this->req_payload;
	}
	public function test() {
		$debug = [];
		// Test selct all
		// $debug = $this->conn->prepare("SELECT * FROM $this->main_table");
		// $debug->execute();
		// $debug->fetchAll(PDO::FETCH_ASSOC);

		// Test select one
		$debug = $this->conn->prepare("SELECT * FROM $this->main_table WHERE " . $this->cols["id"] . " = :id");
		$debug->bindParam(":id", 1);
		$debug->execute();
		var_dump($debug->fetchAll(PDO::FETCH_ASSOC));

		// Test insert
		// $debug = $this->conn->prepare("INSERT INTO $this->main_table (name, password) VALUES (:name, :password)");
		// $debug->bindValue(":name", "test");
		// $pass = password_hash("test", PASSWORD_DEFAULT);
		// $debug->bindValue(":password", $pass);
		// $debug->execute();

		// $debug2 = $this->conn->prepare("SELECT * FROM $this->main_table WHERE $this->id = :id");
		// $debug2->bindValue(":id", $this->conn->lastInsertId());
		// $debug2->execute();
		// var_dump($debug2->fetchAll(PDO::FETCH_ASSOC));

		// Test update
		// $debug = $this->conn->prepare("UPDATE $this->main_table SET name = :name, password = :password WHERE $this->id = :id");
		// $debug->bindValue(":name", "john");
		// $pass = password_hash("test", PASSWORD_DEFAULT);
		// $debug->bindValue(":password", $pass);
		// $debug->bindValue(":id", 1);
		// $debug->execute();

		// $debug2 = $this->conn->prepare("SELECT * FROM $this->main_table WHERE $this->id = :id");
		// $debug2->bindValue(":id", 1);
		// $debug2->execute();
		// var_dump($debug2->fetchAll(PDO::FETCH_ASSOC));
		return;
	}
}

// $aa = new validated();

// // var_dump($aa);
// // echo "<br>";

// $aaR = $aa->valid();

// var_dump($aaR);
// echo "<br>";