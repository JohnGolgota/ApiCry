<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Opener-Policy: same-origin");
// header("Access-Control-Embedder-Policy: require-corp");
$debug = [];
require_once "vendor/autoload.php";
require_once "src/clases/validate.php";
require_once "src/contratos.php";
$authorization = new validated();
$validated          = $authorization->valid();
if (!isset($validated) || !isset($validated["code"]) || $validated["code"] !== 200) {
	$err["Unauthorized"] = "Bad getway";
  http_response_code($validated["code"]??501);
	echo json_encode($err, JSON_UNESCAPED_UNICODE);
	return;
}
$_SESSION["auth_token"] = true;
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
	case 'GET':
		try {
			$limit_int = intval($_GET['limit'] ?? 10);
			$page_int  = intval($_GET['page'] ?? 1);
			$contratos = new ter_t($limit_int, $page_int);
			if (isset($_GET['id'])) {
				$id     = $_GET['id'];
				$result = $contratos->get_by_id($id);
				if ($result) {
					http_response_code(200);
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
					return;
				} else {
					http_response_code(404);
					echo json_encode(array('message' => 'No se encontró el contratos.'), JSON_UNESCAPED_UNICODE);
					return;
				}
				return;
			} else {
				// consulta general
				$result = $contratos->get_all();
				$result += $debug;
				if ($result) {
					http_response_code(200);
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
					return;
				} else {
					http_response_code(404);
					echo json_encode(array('message' => 'No se encontraron contratos`s.'), JSON_UNESCAPED_UNICODE);
					return;
				}
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
			$json_data = file_get_contents("php://input");
      if(!$json_data){
        echo json_encode(["Error" => "No se enviaron datos"], JSON_UNESCAPED_UNICODE);
        break;
      }

			$data = json_decode($json_data, true);

			$contratos = new ter_t();
			$id        = $contratos->insert($data);

			if ($id) {
				http_response_code(201);
				echo json_encode(array('message' => 'contratos registrado correctamente.', 'id_contratos' => $id), JSON_UNESCAPED_UNICODE);
			} else {
				http_response_code(500);
				echo json_encode(array('message' => 'Error al insertar el contratos.'), JSON_UNESCAPED_UNICODE);
			}
		} catch (\Throwable $th) {
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);
		}
		break;

	case 'PUT':
		$json_data = file_get_contents("php://input");
		$data = json_decode($json_data, true);
		try {
			$contratos = new ter_t();
			$result    = $contratos->update($data);
			if ($result) {
				http_response_code(200);
				echo json_encode(array('message' => 'contratos actualizado correctamente.', 'response' => $result), JSON_UNESCAPED_UNICODE);
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
			if (isset($_GET['id'])) {
				$id = $_GET['id'];

				$contratos = new ter_t();
				$result    = $contratos->delete($id);

				if ($result) {
					http_response_code(200);
					echo json_encode($result, JSON_UNESCAPED_UNICODE);
					return;
				} else {
					http_response_code(500);
					echo json_encode(array('message' => 'Error al eliminar el contratos.'), JSON_UNESCAPED_UNICODE);
					return;
				}
			} else {
				http_response_code(400);
				echo json_encode(array('message' => 'El parámetro "id" es requerido para la eliminación.'), JSON_UNESCAPED_UNICODE);
				return;
			}
		} catch (\Throwable $th) {
			echo json_encode(array('message' => $th->getMessage()), JSON_UNESCAPED_UNICODE);
			return;
		}
		break;

	default:
		http_response_code(405);
		echo json_encode(array('message' => 'Método no permitido.'), JSON_UNESCAPED_UNICODE);
		break;
}