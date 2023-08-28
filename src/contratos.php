<?php
require_once "clases/model_helper.php";
class ter_t extends model_helper {
	protected $dbname = "postgres";
	protected $main_table_db = 'tb_man';
	protected $columns = ["id_terminacion_contrato", "fecha_registro", "nombres_apellidos", "identificacion", "email", "link_carta", "estado", "medios_envio", "fecha_ini", "fecha_final", "fecha_aplazado", "observaciones"];
	protected $columns_obj = [
		"id_terminacion_contrato" => "id_terminacion_contrato",
		"fecha_registro" => "fecha_registro",
		"nombres_apellidos" => "nombres_apellidos",
		"identificacion" => "identificacion",
		"email" => "email",
		"link_carta" => "link_carta",
		"estado" => "estado",
		"medios_envio" => "medios_envio",
		"fecha_ini" => "fecha_ini",
		"fecha_final" => "fecha_final",
		"fecha_aplazado" => "fecha_aplazado",
		"observaciones" => "observaciones",
	];
	// TODO validadciones para insertar registro
	protected $columns_required = [
		"id_terminacion_contrato" => [
			"name" => "id_terminacion_contrato",
			"required" => true
		],
		"fecha_registro" => [
			"name" => "fecha_registro",
			"required" => true
		],
		"nombres_apellidos" => [
			"name" => "nombres_apellidos",
			"required" => true
		],
		"identificacion" => [
			"name" => "identificacion",
			"required" => true
		],
		"email" => [
			"name" => "email",
			"required" => true
		],
		"link_carta" => [
			"name" => "link_carta",
			"required" => false
		],
		"estado" => [
			"name" => "estado",
			"required" => true
		],
		"medios_envio" => [
			"name" => "medios_envio",
			"required" => true
		],
		"fecha_ini" => [
			"name" => "fecha_ini",
			"required" => true
		],
		"fecha_final" => [
			"name" => "fecha_final",
			"required" => false
		],
		"fecha_aplazado" => [
			"name" => "fecha_aplazado",
			"required" => false
		],
		"observaciones" => [
			"name" => "observaciones",
			"required" => false
		],
	];
}