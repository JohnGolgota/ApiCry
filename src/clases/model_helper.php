<?php
// header("Content-Type: application/json; charset=UTF-8");
// if (!isset($_SESSION["auth_token"]) || $_SESSION["auth_token"] !== true) {
// 	echo json_encode(array("error" => "Authentication token is required"));
// 	return;
// }
// Wake up
require_once 'database.php';
class model_helper extends Database {
	private $limit;
	private $offset;
	private $all_rows_db;
	protected $main_table_db;
	protected $columns;
	// TODO datos obligatorios
	protected $columns_obj;
	private $err;
	// FIXME borrable
	public $debug_vars;
	public $debug_all;
	protected $columns_required;
	protected $required;
	/**
	 * @version 1.0.1
	 */
	public function __construct($limit = 10, $offset = 1) {
		try {
			parent::__construct($this->dbname ?? DB_NAME);
			$this->are_db_params_valid();
			$this->are_param_valid($limit, $offset);
		} catch (\Throwable $th) {
			$this->err[] = array("message" => "Se detuvo el proceso", "private" => $th->getMessage());
			return;
		}
		return;
	}
	/**
	 * @version 1.0.0
	 */
	public function get_all(): array {
		$res = array();
		// $debug         = array();
		// $debug["this"] = $this->debug_all;
		if (!$this->err === false) {
			$res["error"] = $this->err;
			// $debug["get_all"]["query"]["Errors"] = $this->err;
			// $res += $debug;
			return $res;
		}
		try {
			// TODO order by?
			// Control de exepciones
			$query = "SELECT * FROM $this->main_table_db ORDER BY " . $this->columns[0] . " DESC LIMIT ? OFFSET ?";
			// $debug["get_all"]["query"] = $query;

			// Preparar la consulta
			$stmt = $this->conn->prepare($query);

			// $debug["get_all"]["params"]["this"] = array("limt" => $this->limit, "offset" => $this->offset);
			$stmt->bindParam(1, $this->limit, PDO::PARAM_INT);
			$stmt->bindParam(2, $this->offset, PDO::PARAM_INT);

			// Ejecutar la consulta
			$stmt->execute();

			$resultados["data"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$resultados["rows"] = $this->all_rows_db;
			// $debug["error"] = $this->err;
			$res = $resultados;
			// $debug["get_all"]["results"] = $resultados;

			// $res["debug"] = $debug;
			return $res;
		} catch (\Throwable $th) {
			$this->err[] = array("message" => $th->getMessage(), "private" => $th);
			// $debug["Errors"] = $this->err;
			// $res += $debug;
			return $res;
		}

	}
	/**
	 * @version 1.0.0
	 */
	public function get_by_id($id) {
		$res = [];
		// $res["search_id"] = $id;
		// TODO validar id recibida.
		// $debug = array("id" => $id);
		if (!$this->err === false) {
			$res += array("Error" => $this->err);
			// $res["debug"] = $debug;
			return $res;
		}
		try {
			// Consulta
			$query = "SELECT * FROM $this->main_table_db WHERE " . $this->columns[0] . "=:id";
			// $debug["consulta"] = $query;

			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			$stmt->execute();

			$registro = $stmt->fetch(PDO::FETCH_ASSOC);
			// $debug["results"] = $registro;

			if ($registro) {
				$res["data"] = $registro;
				$res["code"] = 200;
				// $res["debug"] = $debug;
				return $res;
			}
			$res["message"] = "No se a encontrado";
			$res["code"] = 404;
			// $res["debug"] = $debug;
			return $res;
		} catch (\Throwable $th) {
			$this->err[] = array("message" => $th->getMessage(), "private" => $th);
			$res["message"] = $th->getMessage();
			$res["code"] = 500;
			return $res;
		}
	}
	/**
	 * @version 1.0.0
	 */
	public function get_by_match($valor_filtro = 1): array {
		$res = [];
		$debug = array();
		$debug["params"] = array("valor filtro" => $valor_filtro);
		if (!$this->err === false) {
			$res["error"] = $this->err;
			$res += $debug;
			return $res;
		}
		try {
			$coincidencias = array();
			foreach ($this->columns as $field) {
				// $coincidencias[] = $field . " LIKE :" . $field;
				$coincidencias[] = $field . " LIKE :valor_filtro";
			}
			$debug["coincidencias"] = $coincidencias;
			$where = implode(" OR ", $coincidencias);
			$debug["where"] = $where;

			$query = "SELECT * FROM $this->main_table_db WHERE $where ORDER BY " . $this->columns[0] . " DESC LIMIT $this->limit OFFSET $this->offset";
			$debug["consulta"] = $query;

			$stmt = $this->conn->prepare($query);
			// $stmt->bindParam(":valor_filtro", $valor_filtro);
			$stmt->bindValue(":valor_filtro", "%" . $valor_filtro . "%");
			$stmt->execute();

			$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$res["data"] = $resultados;

			$res["rows"] = $this->all_rows_db;

			$res["code"] = 200;
			$res["debug"] = $debug;
			return $res;
		} catch (\Throwable $th) {
			$res["message"] = $th->getMessage();
			$res["code"] = 500;
			$res["debug"] = $debug;
			return $res;
		}
	}
	/**
	 * @version 1.0.0
	 */
	public function insert($data) {
		/**
		 * Respuesta
		 * @var array objeto con la informacion de respesta
		 */
		$res = array();
		/**
		 * @var array $debug objeto con la depuracion de todas las acciones
		 */
		// $debug = array("action" => "Insert new", "method" => "POST");

		if (!$this->err === false) {
			$res["error"] = $this->err;
			// $debug["get_all"]["query"]["Errors"] = $this->err;
			// $res += $debug;
			return $res;
		}
		try {
			// Para lanzar la posibles execpciones necesarias.
			$this->validate_data($data, "INSERT");
			// $debug["req_data"] = $data;

			/**
			 * @var array copia para poder mutar el objeto con los nombres de las columnas en la base de datos.
			 */
			$arr = $this->columns_obj;
			// $debug["arr"]["this"] = $arr;

			// quitamos el primer elemento del objeto
			array_shift($arr);
			// $debug["arr"]["shift"] = $arr;

			/**
			 * @var int $iterator debug
			 * @var int $iner_iterator debug
			 * @var array $binds_params debug
			 */
			// $iterator      = 0;
			// $iner_iterator = 0;

			/**
			 * @var array $set_columns columnas que coinciden con la base de datos
			 * @var array $set_values columnas que coinciden con la base de datos
			 */
			$set_columns = array();
			$set_values = array();
			foreach ($data as $key => $value) {
				// $iterator++;
				if (isset($arr[$key])) {
					// $iner_iterator++;
					// $binds_params[] = array('iteration' => $key,'column name' => $arr[$key],'data value for column' => $data[$arr[$key]],"value" => $value);
					$set_columns[] = $arr[$key];
					$set_values[] = ":" . $arr[$key];
				} else {
					// $binds_params[] = array('iteration' => $key,'message' => "No se encontró índice correspondiente en \$arr");
				}
			}
			/**
			 * @var string $atrs lista de atributos de la base de datos separados por comas
			 * @var string $values valores nombrados como sus respectivos atributos separados por comas con un ':' adelante
			 */
			$atrs = implode(",", $set_columns);
			$values = implode(",", $set_values);

			// $debug["iterated string"]["iterations"]["total"]        = $iterator;
			// $debug["iterated string"]["iterations"]["if statement"] = $iner_iterator;
			// $debug["iterated string"]["binds params"]               = $binds_params;
			// $debug["iterated string"]["result"]["colums"]    = $set_columns;
			// $debug["iterated string"]["result"]["values"]    = $set_values;
			// $debug["iterated string"]["result"]["atributos"] = $atrs;
			// $debug["iterated string"]["result"]["values"]    = $values;

			/**
			 * @var string $query consulta resultante de las iteraciones de arriba
			 */
			$query = "INSERT INTO $this->main_table_db ($atrs) VALUES ($values)";

			// $debug["query"] = $query;

			$stmt = $this->conn->prepare($query);
			/**
			 * @var array $binding debug
			 */
			// $binding = array();
			foreach ($data as $key => $value) {
				if (isset($arr[$key])) {
					// $binding["acepted"][] = "(:" . $key . "),(" . $value . ")";
					$stmt->bindValue(":" . $key, $value);
				} else {
					// $binding["rejected"][] = "(:" . $key . "),(" . $value . ")";
				}
			}
			// $debug["bindings"] = $binding;

			if ($stmt->execute()) {
				$res["id"] = $this->conn->lastInsertId();
				$res["code"] = 201;
				// $res["debug"] = $debug;
				return $res;
			}
			// $res["debug"] = $debug;
			$res["code"] = 400;
			return $res;
		} catch (\Throwable $th) {
			$this->err[] = array("message" => $th->getMessage(), "private" => $th);
			$res["code"] = 500;
			$res["Error"] = $th->getMessage();
			// $debug["Errors"] = $this->err;
			// $res["debug"]    = $debug;
			return $res;
		}
	}
	/**
	 * Aciom de actualizacion
	 * @param mixed $data presunto objeto con informacion para la base de datos
	 * @return array
	 * @version 1.0.0
	 */
	public function update($data): array {
		/**
		 * @var array $res respuesta compuesta para la consulta
		 */
		$res = array();
		// $debug = array();
		if (!$this->err === false) {
			$res["error"] = $this->err;
			// $debug["get_all"]["query"]["Errors"] = $this->err;
			// $res += $debug;
			return $res;
		}
		try {
			$this->validate_data($data, "UPDATE");
			// TODO elemento no existe en la base de datos

			// $debug["data recibida"] = $data;

			// TODO if $data is not array throw err

			/**
			 * @var array $arr columnas contempladas a afectar de la base de datos
			 */
			$arr = $this->columns_obj;
			// $debug["arr"]["arr declare"] = $arr;

			$shift_element = array_shift($arr);
			// $debug["arr"] += array("arr shifted" => $arr, "shifted element" => $shift_element);

			// debug
			// $pop_element = array_pop($arr);
			// $debug["arr"] += array("arr popped" => $arr, "poped element" => $pop_element);

			/**
			 * @var array $binds_params debug
			 */
			// $binds_params = array();
			$set_columns = array();
			foreach ($data as $key => $value) {
				if (isset($arr[$key])) {
					// $binds_params[] = array('iteration' => $key,'column name' => $arr[$key],'data value for column' => $data[$arr[$key]],"value" => $value);
					$set_columns[] = $arr[$key] . "=:" . $arr[$key];
				} else {
					// $binds_params[] = array('iteration' => $key,'message' => "No se encontró índice correspondiente en \$arr");
				}
			}
			/**
			 * @var string $cadena_iterada resultado de la iteracion por el objeto recibido y la comprobacion para afectar las filas
			 */
			$cadena_iterada = implode(",", $set_columns);

			// $debug["cadena iterada"]["binds params"]   = $binds_params;
			// $debug["cadena iterada"]["arr set colums"] = $set_columns;
			// $debug["cadena iterada"]["result"]         = $cadena_iterada;

			/**
			 * @var string $query consulta generada para la actualizacion
			 */
			$query = "UPDATE $this->main_table_db SET $cadena_iterada WHERE $shift_element=:$shift_element";
			// $debug["consulta"] = $query;

			$arr[$shift_element] = $shift_element;
			// $debug["arr"]["pushed"] = $arr;

			$stmt = $this->conn->prepare($query);

			/**
			 * @var array $binding debug
			 * @var int $iterator debug
			 * @var int $iterator_if_statement debug
			 */
			// $binding = array();
			// $iterator = 0;
			// $iterator_if_statement = 0;
			foreach ($data as $key => $value) {
				// $iterator++;
				if (isset($arr[$key])) {
					// $iterator_if_statement++;
					// $binding["acepted"][] = ":" . $key . "," . $value;

					// bindParam enlaza la referencia de la variable a la sentencia preparada. Por lo que si la variable deja de existir como es el caso de las variables declaradas dentro de este foreach la consulta no tendria ningun valor
					$stmt->bindValue(":" . $key, $value);
					// bindValue como expresa su nombre... en laza el valor...
				} else {
					// $binding["ignorados"][] = ":" . $key . "," . $value;
				}
			}
			// $debug["binding"]["iterators"]["total"] = $iterator;
			// $debug["binding"]["iterators"]["if statement"] = $iterator_if_statement;
			// $debug["binding"]["data"] = $binding;

			$stmt->execute();

			$affected_rows = $stmt->rowCount();
			$res["affected"]["rows"] = $affected_rows;
			// $debug["res"]["affected rows"] = $affected_rows;

			$res["affected"]["id"] = $data[$this->columns[0]];

			$res["updated_info"] = $this->get_by_id($data[$this->columns[0]]);

			// $current_data_db               = $this->get_by_id($data[$this->columns[0]]);
			// $debug["res"]["updated info"] = $current_data_db;

			// $res["debug"] = $debug;
			// $res["debug ob"] = $this->debug_all;
			return $res;

		} catch (\Throwable $th) {
			$this->err[] = array("message" => $th->getMessage(), "private" => $th);
			// $debug["Errors"] = $this->err;
			// $res["debug"] = $debug;
			$res["debug ob"] = $this->debug_all;
			$res["Error"] = $this->err;
			return $res;
		}
		// $res["debug"] = $debug;
		// $res["debug ob"] = $this->debug_all;
	}
	/**
	 * @version 1.0.0
	 */
	public function delete($id): array {
		$res = array("action" => "delete", "id" => $id);
		// $debug = array("id req" => $id);
		try {
			// $debug["param"] = array("main table" => $this->main_table_db, "main col" => $this->columns[0]);

			$query = "DELETE FROM $this->main_table_db WHERE " . $this->columns[0] . " = :id";
			// $debug["consulta"] = $query;

			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(':id', $id);
			$stmt->execute();

			$filas_afectadas = $stmt->rowCount();
			// $debug["filas afectadas"] = $filas_afectadas;

			$res["affected_rows"] = $filas_afectadas;
			if ($filas_afectadas === 1) {
				$res["code"] = 200;
				return $res;
			}
			// $res["debug"]         = $debug;
			$res["code"] = 404;
			return $res;
		} catch (PDOException $error) {
			$this->err[] = array("message" => $error->getMessage(), "private" => $error);

			// $debug["Errors"] = $this->err;
			// $res["debug"]    = $debug;

			$res["Error"] = $error->getMessage();
			$res["code"] = 500;
			return $res;
		}
	}
	/**
	 * @version 1.0.0
	 * @return array Retorna un objeto con el numero de filas que hay en la tabla principal de la base de datos
	 */
	private function get_total_rows(): array {
		// $debug = array();
		$res = array();

		try {
			$query = "SELECT COUNT(*) as total_rows FROM $this->main_table_db";
			// $debug["get_total_rows"]["query"] = $query;

			$stmt = $this->conn->prepare($query);
			$stmt->execute();

			$res = $stmt->fetch(PDO::FETCH_ASSOC);

			// $debug["get_total_rows"]["result"] = $res;
			// $res["debug"] = $debug;

			return $res;
		} catch (\Throwable $th) {
			$this->err[] = array("message" => "No se encontraron registros", "private" => $th);
			// $debug["get_total_rows"]["errors"] = $th;
			// $debug["get_total_rows"]["errors"] = $this->err;

			$res["total_rows"] = 0;
			// $res["debug"] = $debug;
			return $res;
		}
	}
	/**
	 * Lanza ecepciones en caso de que los parametros no sean validos
	 * @version 1.0.1
	 * @todo Dale mas vueltas a esto
	 */
	private function are_param_valid($limit, $offset): void {
		// $debug                   = array();
		// $debug["params"]["init"] = array("limit" => $limit, "offset" => $offset);
		if (!is_integer($limit)) {
			// $debug["params"]["limit"]["type"][] = array("type" => gettype($limit), "value" => $limit, "use_default" => true, "default value" => 1);
			$limit = 1;
			throw new Exception("Tiene que ser un numero");
		}
		if (!is_integer($offset)) {
			// $debug["params"]["offset"]["type"][] = array("type" => gettype($offset), "value" => $offset, "use_default" => true, "default value" => 10);
			$offset = 10;
			throw new Exception("nel man tiene que ser un numero");
		}
		if ($offset <= 0) {
			// $debug["params"]["offset"]["val"][] = array("value" => $offset, "use_default" => true, "default value" => 1, "debe ser mayor que cero");
			$offset = 1;
			throw new Exception("pagina no puede ser cero o menor de cero");
		}
		if ($limit <= 0) {
			// $debug["params"]["limit"]["val"][] = array("value" => $limit, "use_default" => true, "default value" => 10, "debe ser mayor que cero");
			$limit = 10;
			throw new Exception("Limite no puede ser cero o menor de cero");
		}
		try {
			$total = $this->get_total_rows();
			// $debug["params"]["total"] = $total;

			$this->all_rows_db["total_rows"] = $total["total_rows"];
			// $debug["params"]["all_rows_db"]  = $this->all_rows_db;

			$this->offset = ($offset - 1) * $limit;
			// $debug["params"]["offset"]["this"] = $this->offset;

			$this->all_rows_db["pages"] = ceil($total["total_rows"] / $limit);
			// $debug["params"]["all_rows_db"]["pages"] = $this->all_rows_db;

			if ($this->offset > $this->all_rows_db["total_rows"]) {
				// $debug["errors"]["page_not_found"] = "404";
				// $debug["errors"]["fuc"]            = array("fali" => $this->all_rows_db, "fail 2" => $this->offset, "calc" => $this->offset > $this->all_rows_db["pages"]);
				throw new Exception("No existe esta pagina");
			}
		} catch (\Throwable $th) {
			$this->err[] = array("message" => "Pagina no encontrada, " . $th->getMessage(), "private" => $th);
			// $debug["errors"]["this"] = $this->err;
			// $this->debug_all         = $debug;
			return;
		}
		$this->all_rows_db["page"] = $offset;
		// $debug["params"]["all_rows_db"]["page"] = $this->all_rows_db["page"];

		$this->all_rows_db["rows_per_page"] = $limit;
		// $debug["params"]["all_rows_db"]["rows_per_page"] = $this->all_rows_db["rows_per_page"];

		$this->limit = $limit;
		// $debug["params"]["this"]["limit"] = $this->limit;
		// $this->debug_all                  = $debug;
		return;
	}
	/**
	 * @version 1.0.0
	 */
	private function are_db_params_valid(): void {
		if (!$this->columns) {
			throw new Exception("Falta la lista de columnas de la base de datos");
		}
		if (!$this->columns_obj) {
			throw new Exception("Falta un objeto con las columnas de la base de datos");
		}
		if (!$this->main_table_db) {
			throw new Exception("Falta tabla el nombre de la tabla de la base de datos");
		}
		return;
	}
	/**
	 * comprueba que el objeto con la informacion recibida cumpla con los requisitos los parametros minimos para operar.
	 * @param mixed $data presunto objeto con la informacion a enviar a la base de datos
	 * @return void
	 * @version 1.0.0
	 * @todo hacerlo Xd
	 */
	private function validate_data($data, string $opt = "INSERT"): void {

		// $this->debug_all["req"]             = $this->required;
		$this->debug_all["data recived"] = $data;
		$this->debug_all["option selected"] = $opt;
		if (!is_array($data)) {
			throw new Exception("El dato recibido debe ser un objeto   ");
		}
		// $errors = array();
		// switch ($opt) {
		// 	case 'INSERT':
		// 		foreach ($this->columns_required as $fieldName => $fieldConfig) {
		// 			$this->debug_all["foreach"][] = array("field name" => $fieldName, "field config" => $fieldConfig);
		// 			if ($fieldConfig["required"] && !isset($data[$fieldName])) {
		// 				$errors[] = "Missing " . $fieldName;
		// 			}
		// 		}
		// 		$this->debug_all["errs"] = $errors;
		// 		break;
		// 	case 'UPDATE':
		// 		if (!isset($data[$this->columns[0]])) {
		// 			$errors[] = "Missing " . $this->columns[0];
		// 		}
		// 		$this->debug_all["errs"] = $errors;
		// 		break;
		// 	default:
		// 		throw new Exception("Why you do dat?");
		// 		break;
		// }
		// if (!empty($errors)) {
		// 	throw new Exception(implode(", ", $errors));
		// }
		return;
	}
	private function validate_id($id): void {
		// TODO
	}
}