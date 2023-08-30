<?php
// header("Content-Type: application/json; charset=UTF-8");
// if (!isset($_SESSION["auth_token"]) || $_SESSION["auth_token"] !== true) {
// 	echo json_encode(array("error" => "Authentication token is required"));
// 	return;
// }
function def_env_consts_ifs($args) {
	$lines_args = explode("\n", $args);
	foreach ($lines_args as $key => $value) {
		$def_arg = explode("=", $value);
		$_ENV += array($def_arg[0] => trim($def_arg[1]));
		if ($def_arg[0] == "API_KEY") {
			define("API_KEY", trim($def_arg[1]));
		} elseif ($def_arg[0] == "DB_HOST") {
			define("DB_HOST", trim($def_arg[1]));
		} elseif ($def_arg[0] == "DB_USERNAME") {
			define("DB_USERNAME", trim($def_arg[1]));
		} elseif ($def_arg[0] == "DB_PASSWORD") {
			define("DB_PASSWORD", trim($def_arg[1]));
		} elseif ($def_arg[0] == "DB_NAME") {
			define("DB_NAME", trim($def_arg[1]));
		} else {
			define($def_arg[0], trim($def_arg[1]));
		}
	}
}
function const_from_env() {
	if ($_ENV["API_KEY"] == null || $_ENV["DB_HOST"] == null || $_ENV["DB_USERNAME"] == null || $_ENV["DB_PASSWORD"] == null || $_ENV["DB_NAME"] == null) {
		return;
	} else {
		define("API_KEY", $_ENV["API_KEY"]);
		define("DB_HOST", $_ENV["DB_HOST"]);
		define("DB_USERNAME", $_ENV["DB_USERNAME"]);
		define("DB_PASSWORD", $_ENV["DB_PASSWORD"]);
		define("DB_NAME", $_ENV["DB_NAME"]);
		define("API_DB_TABLE", $_ENV["API_DB_TABLE"]);
		define("VALIDATE_DB", $_ENV["VALIDATE_DB"]);
		define("VALIDATE_DB_TABLE", $_ENV["VALIDATE_DB_TABLE"]);
		define("ALGORITHM", $_ENV["ALGORITHM"]);
	}
}
function const_from_text() {
	define("API_KEY", "MamaHuevoDigoGluGluGlu");
	define("DB_HOST", "localhost");
	define("DB_PASSWORD", '');
	define("DB_USERNAME", "postgres");
	define("DB_NAME", "postgres");
	define("API_DB_TABLE", "tbl_example");
	define("VALIDATE_DB", "postgres");
	define("VALIDATE_DB_TABLE", "tbl_app_client");
	define("ALGORITHM", "HS256");
}
function def_env_consts($args) {
	$lines_args = explode("\n", $args);
	foreach ($lines_args as $key => $value) {
		$def_arg = explode("=", $value);
		$_ENV[$def_arg[0]] = trim($def_arg[1]);
		define($def_arg[0], trim($def_arg[1]));
	}
}
// const_from_env();
// const_from_text();
// def_env_consts_ifs(file_get_contents($_ENV["env_file"]));
def_env_consts(file_get_contents($_ENV["env_file"]));
try {
	require_once $_ENV["const_path"];
} catch (\Throwable $th) {
	// const COLS_VALID_TABLE = [
	// ];
	// const COLS_API_TABLE = [
	// ];
	// const COLS_API_TABLE_OBJ = [
	// ];
}