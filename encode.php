<?php
header('Content-Type: application/json; charset=utf-8');

require_once "vendor/autoload.php";
require_once "src/clases/validate.php";
$debug = [];

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);
// $debug["data"] = $data;
// $debug["data type"] = gettype($data);

$authorization = new validated();
// $authorization->test();
// TODO usar post
if (isset($_GET["new_client"]) && $_GET["new_client"] == "true") {
	$validated = $authorization->valid();
	// $debug["valid"] = $validated;
	if (!isset($validated) || !isset($validated["code"]) || $validated["code"] !== 200) {
		$err["Unauthorized"] = "Bad getway";
		http_response_code($validated["code"] ?? 501);
		echo json_encode($err, JSON_UNESCAPED_UNICODE);
		// echo json_encode($debug, JSON_UNESCAPED_UNICODE);
		return;
	}
	$newClient = $authorization->sendNewClient($data);
	http_response_code($newClient["code"] ?? 500);
	// $debug["newClient"] = $newClient;
	echo json_encode($debug, JSON_UNESCAPED_UNICODE);
	return;
}
if (isset($_GET["new_token"]) && $_GET["new_token"] == "true") {
	$newToken = $authorization->newToken($data);
	http_response_code($newToken["code"] ?? 500);
	$debug["newToken"] = $newToken;
	echo json_encode($debug, JSON_UNESCAPED_UNICODE);
	return;
}
if (isset($_GET["test"]) && $_GET["test"] == "true") {
	$authorization->test();
}