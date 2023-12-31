<?php
require_once "clases/model_helper.php";
class api_final extends model_helper {
	protected $dbname = DB_NAME;
	protected $main_table_db = API_DB_TABLE;
	protected $columns = COLS_API_TABLE;
	protected $columns_obj = COLS_API_TABLE_OBJ;
	// TODO validadciones para insertar registro
	protected $columns_required = [
	];
}
class suggests extends model_helper {
	// protected $dbname = DB_NAME;
	// protected $main_table_db = API_DB_TABLE;
	// protected $columns = COLS_API_TABLE;
	// protected $columns_obj = COLS_API_TABLE_OBJ;
	// TODO validadciones para insertar registro
	// protected $columns_required = [

	// ];
	public function __construct($arr = null) {
		$this->dbname = $arr["dbname"] ?? DB_NAME;
		$this->main_table_db = $arr["main_table_db"] ?? API_DB_TABLE;
		$this->columns = $arr["columns"] ?? COLS_API_TABLE;
		$this->columns_obj = $arr["columns_obj"] ?? COLS_API_TABLE_OBJ;
		$this->columns_required = $arr["columns_required"] ?? [];
		parent::__construct($arr["limit"] ?? 10, $arr["page"] ?? 1);
	}
		// TODO
	private function suggest() {
		$OBJETO = [
			"dbname" => "db",
			"main_table_db" => "tabla",
			"columns" => [
				"id",
				"nombre",
				"created_at",
				"updated_at",
				"deleted_at",
			],
			"columns_obj" => [
				"id" => "id",
				"nombre" => "nombre",
				"created_at" => "created_at",
				"updated_at" => "updated_at",
				"deleted_at" => "deleted_at",
			],
		];
		$e = new endpoint(OBJETO);
		$res = $e->get_all();
		return $res;
	}
}