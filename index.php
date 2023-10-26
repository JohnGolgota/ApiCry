<?php
header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Opener-Policy: same-origin");
// header("Access-Control-Embedder-Policy: require-corp");
$debug = [];
//$debug["headers"] = getallheaders();
//$debug["a2"] = $debug["headers"]["authorization2"];
//$debug["A2"] = $debug["headers"]["Authorization2"];
//$debug["glbals"] = $GLOBALS;
//$debug["env"] = $_ENV;
//$debug["neko"] = "mimi";

$method = $_SERVER['REQUEST_METHOD'];
if ($method === "OPTIONS") {
	http_response_code(200);
	return;
}
require_once "vendor/autoload.php";
require_once "src/clases/validate.php";
require_once "src/api_final.php";
$authorization = new validated();
$validated = $authorization->valid();
//$debug["valid"] = $validated;
if (!isset($validated) || !isset($validated["code"]) || $validated["code"] !== 200) {
	$err["Unauthorized"] = "Bad getway";
	http_response_code($validated["code"] ?? 501);
	echo json_encode($err, JSON_UNESCAPED_UNICODE);
	//	echo json_encode($debug, JSON_UNESCAPED_UNICODE);
	return;
}

switch ($method) {
	case 'GET':
		try {
			$limit_int = intval($_GET['limit'] ?? 10);
			$page_int = intval($_GET['page'] ?? 1);

			$api_REST = new api_final();


			if (isset($_GET['id']) && !empty($_GET['id'])) {
				$id = $_GET['id'];

				$result = $api_REST->get_by_id($id);

				if (isset($result["data"])) {
					http_response_code($result["code"] ?? 200);
					echo json_encode(["data" => $result["data"]], JSON_UNESCAPED_UNICODE);

					return;
				} else {
					http_response_code($result["code"] ?? 500);
					echo json_encode(array('message' => 'Algo salio mal. ' . $result['message']), JSON_UNESCAPED_UNICODE);
					return;
				}
			}
			if (isset($_GET['search']) && !empty($_GET['search'])) {
				$result = $api_REST->get_by_match($_GET['search']);
				http_response_code($result["code"] ?? 500);
				if (isset($result["debug"])) {
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
					return;
				}
				echo json_encode($result["res"], JSON_UNESCAPED_UNICODE);
				return;
			}

			// consulta general
			$result = $api_REST->get_all($limit_int, $page_int);
			// $result["debug"] = $debug;

			if ($result) {
				http_response_code(200);
				echo json_encode($result, JSON_UNESCAPED_UNICODE);

				return;
			} else {
				http_response_code(500);
				echo json_encode(array('message' => 'Algo salio mal.'), JSON_UNESCAPED_UNICODE);

				return;
			}
			return;

		} catch (\Throwable $th) {
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);

			return;
		}
		break;

	case 'POST':
		try {
			// TODO esto no tiene returns xd
			$json_data = file_get_contents("php://input");
			if (!$json_data) {
				echo json_encode(["Error" => "No se enviaron datos"], JSON_UNESCAPED_UNICODE);
				break;
			}

			$data = json_decode($json_data, true);

			$api_REST = new api_final();
			$id = $api_REST->insert($data);

			if ($id["code"] === 201) {
				http_response_code(201);
				echo json_encode(array('new_id' => $id["id"]), JSON_UNESCAPED_UNICODE);
			} else {
				http_response_code(500);
				echo json_encode(array('message' => 'Error al insertar.', "debug" => $id), JSON_UNESCAPED_UNICODE);
			}
		} catch (\Throwable $th) {
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);
		}
		break;

	case 'PUT':
		$json_data = file_get_contents("php://input");
		$data = json_decode($json_data, true);
		try {
			$api_REST = new api_final();
			$result = $api_REST->update($data);
			if ($result) {
				http_response_code(200);
				echo json_encode(array('message' => 'Actualizado correctamente.', 'updated_info' => $result["updated_info"]["data"] ?? $result), JSON_UNESCAPED_UNICODE);
				return;
			} else {
				http_response_code(500);
				echo json_encode(array('message' => 'Error .'), JSON_UNESCAPED_UNICODE);
				return;
			}
			return;
		} catch (\Throwable $th) {
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);
			return;
		}
		break;

	case 'DELETE':
		try {
			// FIX mal implementacion de metodos. No combinar DELETE con GET... basicamente
			$json_data = file_get_contents("php://input");
			// $debug["json_data"] = $json_data;
			$data = json_decode($json_data, true);
			// $debug["data"] = $data;
			if (isset($data["id"])) {

				$id = $data["id"];
				// $debug["id type"] = gettype($id);
				if (!is_int($id)) {
					// echo json_encode(["Error" => $debug], JSON_UNESCAPED_UNICODE);
					echo json_encode(array('message' => 'El parámetro "id" es requerido para la eliminación.'), JSON_UNESCAPED_UNICODE);
					return;
				}
				$api_REST = new api_final();
				$result = $api_REST->delete($id);
				// $result = $debug;

				if ($result) {
					http_response_code($result["code"]);
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
					return;
				} else {
					http_response_code(500);
					echo json_encode(array('message' => 'Error al eliminar.'), JSON_UNESCAPED_UNICODE);
					return;
				}
			} else {
				http_response_code(400);
				// echo json_encode(["Error" => $debug], JSON_UNESCAPED_UNICODE);
				echo json_encode(array('message' => 'El parámetro "id" es requerido para la eliminación.'), JSON_UNESCAPED_UNICODE);
				return;
			}
		} catch (\Throwable $th) {
			http_response_code(500);
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);
			return;
		}
		break;
	case 'OPTIONS':
		http_response_code(200);
		break;

	default:
		http_response_code(405); // Method Not Allowed
		echo json_encode(array('message' => 'Método no permitido.'), JSON_UNESCAPED_UNICODE);
		break;
}