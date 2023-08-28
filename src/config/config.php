<?php
// header("Content-Type: application/json; charset=UTF-8");
// if (!isset($_SESSION["auth_token"]) || $_SESSION["auth_token"] !== true) {
// 	echo json_encode(array("error" => "Authentication token is required"));
// 	return;
// }
// Configuración de la base de datos
// define('DB_HOST', 'localhost');
// define('DB_USERNAME', 'root');
// define('DB_PASSWORD', '');
// define('DB_NAME', 'pqrs');
// $env = file_get_contents(".env");
// var_dump($env);
// echo "<br>";

function env_def($args) {
	$lines_args = explode("\n", $args);
	// var_dump($lines_args);
	foreach ($lines_args as $key => $value) {
		// echo "<br>key<br>";
		// var_dump($key);
		// echo "<br>value<br>";
		// var_dump($value);
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
function const_from_env(){
  define("API_KEY", $_ENV["API_KEY"]);
  define("DB_HOST", $_ENV["DB_HOST"]);
  define("DB_USERNAME", $_ENV["DB_USERNAME"]);
  define("DB_PASSWORD", $_ENV["DB_PASSWORD"]);
  define("ALGORITHM", $_ENV["ALGORITHM"]);
  // define("DB_NAME", $_ENV["DB_NAME"]);
}
const_from_env();
// env_def(file_get_contents(__DIR__ . "/.env"));
// var_dump(API_KEY);
// var_dump(NOSE_COMO);
?>