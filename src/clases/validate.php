<?php
// require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// var_dump(JWT::class);

require_once "model_helper.php";
// require_once "jwt.php";
class validated extends Database {
	private $main_table = VALIDATE_DB_TABLE;
	private $cols = COLS_VALID_TABLE;
	private $debug;
	private $req_payload;
	private $client_name;
	private $client_pass;
	// private $access = "pass_auth_users";
	// private $access = 1;
	function __construct() {
		parent::__construct(VALIDATE_DB);
	}
	private function validateToken(): array {
		$res = [];
		// $debug = [];
		try {

			$info = $this->getToken();
			// $debug["getToken"]["res"] = $info;
			// $debug["getToken"]["res"]     = $info->getMessage();
			// $debug["getToken"]["type of"] = gettype($info);

			$this->are_info_valid($info);

			$query = "SELECT * FROM $this->main_table WHERE " . $this->cols["id"] . " = :id";
			// $debug["query"] = $query;

			$stm = $this->conn->prepare($query);
			$stm->bindParam(":id", $info->data);
			// TODO info name por que puedo
			$stm->execute();

			$res["rows"] = $stm->fetchColumn();
			$res["match"] = $res["rows"] === intval($info->data);
			// $debug["rows"] = $res;

			// $res += $debug;
			// echo json_encode($debug);
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
			$hea = apache_request_headers();
			// $debug["hea apache"] = $hea;
			// echo json_encode($debug);

			if (!isset($hea["Authorization"]) || empty($hea["Authorization"])) {
				throw new Exception("Seriedad por favor");
			}

			$token_arr = explode(" ", $hea["Authorization"]);
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

			$decoded = JWT::decode($token, new Key(API_KEY, ALGORITHM));
			// $decoded = JWT::decode($token, API_KEY, ALGORITHM);

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
		$res = [];
		// $debug = [];
		try {
			$vali = $this->validateToken();
			// $debug["validateToken"] = $vali;
			// echo json_encode($debug);
			if (isset($vali["err"]) || empty($vali["rows"]) || $vali["match"] !== true) {
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
			$res = ["Error" => $th->getMessage(), "code" => 500];
			return $res;
		}

	}
	public function newToken($req_body): array {
		$debug = [];
		$res["code"] = 200;

		$debug["req_body"] = $req_body;

		try {
			$this->are_req_body_valid($req_body);
			$db = $this->get_valid_user();
			$debug["db"] = $db;

			if (!$db || empty($db) || !isset($db[$this->cols["password"]])) {
				$res["code"] = 401;
				$res["msg"] = "Usuario incorrecto";
				$res["debug"] = $debug;
				return $res;
			}
			if (!password_verify($req_body["password"], $db[$this->cols["password"]])) {
				$debug["password_verify"] = password_verify($req_body["password"], $db[$this->cols["password"]]);
				$res["code"] = 401;
				$res["msg"] = "Password incorrecto";
				$res["debug"] = $debug;
				return $res;
			}
			$debug["password_verify"] = password_verify($req_body["password"], $db[$this->cols["password"]]);

			$payload = [
				"data" => $db[$this->cols["id"]],
				"name" => $db[$this->cols["name"]],
			];
			$debug["payload"] = $payload;

			$jwt = JWT::encode($payload, API_KEY, ALGORITHM);
			$debug["jwt"] = $jwt;

			$res["debug"] = $debug;
			$res["token"] = $jwt;
			return $res;
		} catch (\Throwable $th) {
			$debug["err"] = $th;
			$res["Error"] = $th->getMessage();
			$res["code"] = 401;
			$res["debug"] = $debug ?? "";
			return $res;
		}
	}
	private function get_valid_user(): array {
		$debug = [];
		$sql = "SELECT * FROM $this->main_table WHERE " . $this->cols["name"] . " = :name AND " . $this->cols["id"] . " = :id";
		$debug["sql"] = $sql;
		$stm = $this->conn->prepare($sql);
		$stm->bindParam(":name", $this->req_payload["name"]);
		$stm->bindParam(":id", $this->req_payload["id"]);
		$stm->execute();
		$db = $stm->fetch(PDO::FETCH_ASSOC);
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
		if (!isset($req_body["name"]) || !$req_body["name"]) {
			$this->debug["req_body"] = "name is not set or is empty";
			throw new Exception("Fail request");
		}
		if (!isset($req_body["password"]) || !$req_body["password"]) {
			$this->debug["req_body"] = "password is not set or is empty";
			throw new Exception("Fail request");
		}
		$this->req_payload = $req_body;
		$this->debug["req_payload"] = $this->req_payload;
	}
	public function sendNewClient($data): array {
		// TODO validar que no exista el usuario
		// $debug = [];
		$res = [];
		// $debug["client_name"] = $data["name"];
		// $debug["client_pass"] = $data["password"];
		try {
			if (!isset($data)) {
				$res["code"] = 401;
				$res["msg"] = "missing data";
				// $res["debug"] = $debug;
				return $res;
			}
			if ($data["name"] == null || $data["password"] == null) {
				$res["code"] = 401;
				$res["msg"] = "missing data";
				// $res["debug"] = $debug;
				return $res;
			}
			$this->client_name = $data["name"];
			$this->client_pass = password_hash($data["password"], PASSWORD_DEFAULT);
			$res["new_client"] = $this->newClient();
			$res["code"] = 200;
			$res["msg"] = "new client created";
			// $res["debug"] = $debug;
			return $res;
		} catch (\Throwable $th) {
			$res["code"] = 500;
			$res["msg"] = "error" . $th->getMessage();
			// $res["debug"] = $debug;
			return $res;
		}
	}
	private function newClient(): array {
		try {
			//code...
			// $debug = [];
			$debug = $this->conn->prepare("INSERT INTO $this->main_table (" . $this->cols["name"] . "," . $this->cols["password"] . ") VALUES (:name, :password)");
			$debug->bindParam(":name", $this->client_name);
			$debug->bindParam(":password", $this->client_pass);
			$debug->execute();
			$last_id = $this->conn->lastInsertId();
			$debug2 = $this->conn->prepare("SELECT " . $this->cols["name"] . " name, " . $this->cols["id"] . " id FROM $this->main_table WHERE " . $this->cols["id"] . " = :id");
			$debug2->bindParam(":id", $last_id);
			$debug2->execute();
			return $debug2->fetchAll(PDO::FETCH_ASSOC);
		} catch (\Throwable $th) {
			$res["code"] = 500;
			$res["msg"] = "error" . $th->getMessage();
			// $res["debug"] = $debug;
			return $res;
		}
	}
	public function test() {
		$debug = [];
		// Test selct all
		$debug = $this->conn->prepare("SELECT * FROM $this->main_table LIMIT 2");
		$debug->execute();
		$res = $debug->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($res);
		echo json_encode(array("test data" => $res), JSON_FORCE_OBJECT);
		// echo json_encode($res, JSON_UNESCAPED_UNICODE);

		// Test select one
		// $debug = $this->conn->prepare("SELECT * FROM $this->main_table WHERE " . $this->cols["id"] . " = :id");
		// $debug->bindParam(":id", 1);
		// $debug->execute();
		// var_dump($debug->fetchAll(PDO::FETCH_ASSOC));

		// Test insert
		return;
		$debug = $this->conn->prepare("INSERT INTO $this->main_table (" . $this->cols["name"] . "," . $this->cols["password"] . ") VALUES (:name, :password)");
		$debug->bindValue(":name", "test");
		$pass = password_hash("test", PASSWORD_DEFAULT);
		$debug->bindValue(":password", $pass);
		$debug->execute();

		$debug2 = $this->conn->prepare("SELECT * FROM $this->main_table WHERE " . $this->cols["id"] . " = :id");
		$debug2->bindValue(":id", $this->conn->lastInsertId());
		$debug2->execute();
		// echo json_encode($debug2->fetchAll(PDO::FETCH_ASSOC));
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