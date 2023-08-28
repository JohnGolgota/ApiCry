<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Opener-Policy: same-origin");
header("Access-Control-Embedder-Policy: require-corp");
// $arr = ["hola" => "hola mundo"];
$method = $_SERVER['REQUEST_METHOD'];
$arr["method"] = $method;
// $arr["globals"] = $GLOBALS;
// $arr["apache"] = apache_request_headers();
$arr["hea"] = getallheaders();

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);
$arr["data"] = $data;
// $arr["env"] = $_ENV;
// $pg = pg_connect("user=postgres password=".$_ENV["PASSWORD_PG_SUPABASE"]." host=db.llivepdsawoesnmzcwum.supabase.co port=5432 dbname=postgres");
$host="db.llivepdsawoesnmzcwum.supabase.co";
$db="postgres";
$tpg = new PDO("pgsql:host=$host;port=5432;dbname=$db;","postgres",$_ENV["PASSWORD_PG_SUPABASE"],[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);



$sql = "select * from tb_users ;";
$arr["sql"] = $sql;

$stmt = $tpg->prepare($sql);

$stmt->execute();

$arr["data"]=$stmt->fetchAll(PDO::FETCH_ASSOC);

// $res = pg_query($sql);
// $arr["res"] = $res;

// $fe = pg_fetch_row($res);
// $arr["fe"]=$fe;
echo json_encode($arr, 256);