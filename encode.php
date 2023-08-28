<?php
header('Content-Type: application/json; charset=utf-8');

require_once "vendor/autoload.php";
require_once "src/clases/validate.php";
$debug = [];

$json_data     = file_get_contents("php://input");
$data          = json_decode($json_data, true);
$debug["data"] = $data;

$validated         = new validated();
$newToken          = $validated->newToken($data);
$debug["newToken"] = $newToken;

echo json_encode($debug);