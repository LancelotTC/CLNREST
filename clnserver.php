<?php

include_once "fonctions.php";

$method = $_SERVER['REQUEST_METHOD'];
error_reporting(E_ERROR | E_PARSE);

$request_time = date("Y/m/d H:i:s");
file_put_contents("logs.txt", "$request_time - $method request: ", FILE_APPEND);

$br = "</br>";



function sanitize_text($s) {
    $result = preg_replace("/[^a-zA-Z0-9'\*_]+/", " ", html_entity_decode($s, ENT_QUOTES));
    return $result;
}

function sanitize_int($s) {
	$result = preg_replace("/\D/", "", html_entity_decode($s, ENT_QUOTES));
	return $result;
}

function send_response($code, $message, $result=null) {
	global $method;
	$response = array(
		'code' => $code,
		'method' => $method,
		'message' => $message,
		'result' => $result
	);
	$result = json_encode($result);
	file_put_contents("logs.txt", "$code: $message -> $result\n", FILE_APPEND);


	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	die();
}

$tables_result = sanitize_text($_GET['tables']);
$id = sanitize_int($_GET["id"]);
$data = json_decode($_GET["data"], true);


// $tables_result = filter_input(INPUT_GET,'tables', FILTER_SANITIZE_STRING);

$params = '';

$param_array = array("label", "latitude", "longitude");

switch ($tables_result) {
	case 'plant':
		$tables = array("plant" => "plant_id", );
		array_push($param_array, "growth_state_id", "leaf_amount");
		break;
		
	case 'fruit_tree':
		$tables = array("fruit_tree" => "fruit_tree_id", );
		break;
		
	case 'filter':
		$tables = array("filter" => "filter_id", );
		break;
		
	case 'composter':
		$tables = array("composter" => "composter_id", );
		break;
	case 'growth_state':
		$tables = array("growth_state", );
		$param_array = array("label");
		break;

	case '*':
		$tables = array(
			"plant" => "plant_id",
			"fruit_tree" => "fruit_tree_id",
			"filter" => "filter_id",
			"composter" => "composter_id"
		);
		break;

	default: send_response(403, "Unknown table: '$tables_result'"); die();
}

//refers to the plain list of values to refer to when adding a model for example
// ex: column1, column2, column3
$structure = "";

//refers to the param names to bind
// ex: :column1, :column2, :column3
$values = "";

//refers to the association of both param names and param binds
// ex: column1 = :value1, column2 = :value2
$structure_values = "";

foreach($param_array as $param) {
	if (isset($data[$param])) {
		$structure .= "$param, ";
		$values .= ":$param, ";
		$structure_values .= "$param = :$param, ";
	}
}

$structure = substr($structure, 0, -2);
$values = substr($values, 0, -2);
$structure_values = substr($structure_values, 0, -2);


// $structure = substr($structure, 0, -2);
// $values = substr($values, 0, -2);

// define('PLANT_PARAMS', '(:label, :latitude, :longitude, :growth_state_id)');
// define('TREE_PARAMS', '(:label, :latitude, :longitude)');
// define('FILTER_PARAMS', '(:label, :latitude, :longitude)');
// define('COMPOSTER_PARAMS', '(:label, :latitude, :longitude)');


// $label = sanitize_text($_GET['label']);
// $latitude = sanitize_double($_GET['latitude']);
// $longitude = sanitize_double($_GET['longitude']);


function retrieve_models() {
	global $cnx, $tables;


	foreach ($tables as $key_table => $_) {
		try {
			$request = $cnx->prepare("select * from $key_table");
			$request->execute();
		} catch (Exception $e) {
			send_response(500, "Invalid request: $request", "");
			die();
		}

		$response[$key_table] = $request->fetchAll(PDO::FETCH_ASSOC);
		// array_push($response[$key_table], $request->fetchAll(PDO::FETCH_ASSOC));
	}
	send_response(200, "", $response);
}

function add_model() {
	global $cnx, $tables, $data, $structure, $values, $br;

	if (count($tables) != 1) {
		send_response(403, "Incorrect number of tables: expected 1, got " . count($tables));
	}

	if ($data == null) {
		send_response(403, "Required data, got null");
	}

	# Gets the only table and stores it in $table
	foreach ($tables as $key_table => $_) {
		$table = $key_table;
		break;
	}

	$request = "insert into $table ($structure) values ($values)";

	try {
		$request = $cnx->prepare($request);
		$request->execute($data);

	} catch (Exception $e) {
		send_response(500, "Invalid request: $request because $e", "");
		die();
	}

	if ($request) {
		send_response(200, "Success", $request);
	} else {
		send_response(400, "Fail", $data);
	}
}

function update_model() {
	global $cnx, $tables, $data, $values, $id, $br, $structure_values;

	if (count($tables) != 1) {
		send_response(403, "Incorrect number of tables: expected 1, got " . count($tables));
	}

	if ($id == null) {
		send_response(403, "Required id, got null");
	}

	if ($data == null) {
		send_response(403, "Required data, got null");
	}


	foreach ($tables as $key_table => $value_id_name) {
		$table = $key_table;
		$id_name = $value_id_name;
		break;
	}

	$request = "update $table set $structure_values where $id_name = $id;";
	
	try {
		$request = $cnx->prepare($request);
		foreach ($data as $key => $value) {
			$request->bindValue(":$key", $value);
		}

		$request->execute();
		
	} catch (Exception $e) {
		send_response(500, "Invalid request: $request because $e", "");
		die();
	}

	if ($request) {
		send_response(200, "Success", $data);
	} else {
		send_response(400, "Fail", $request);
	}
}

function delete_model() {
	global $cnx, $tables, $id;
	$table = $tables[0];

	if (count($tables) != 1) {
		send_response(403, "Incorrect number of tables: expected 1, got " . count($tables));
	}

	if ($id == null) {
		send_response(403, "Required id, got null");
	}

	foreach ($tables as $key_table => $value_id_name) {
		$table = $key_table;
		$id_name = $value_id_name;
	}

	$request = "delete from $table where $id_name = $id;";

	try {
		$request = $cnx->prepare($request);
		$request->execute();
	} catch (Exception $e) {
		send_response(500, "Invalid request: $request because $e", "");
	}

	if ($request) {
		send_response(200, "Success", $request);
	} else {
		send_response(400, "Fail", $request);
	}
}

function is_defined($param) {
	return $param != '' && isset($param);
}

try {
	$cnx = connexionPDO();

	switch ($method) {
		case 'GET':
			retrieve_models(); break;
		case 'POST':
			add_model(); break;
		case 'PUT':
			update_model(); break;
		case 'DELETE':
			delete_model(); break;
		default:
			send_response("403", "Unsupported request method: $method", "");
		}
} catch (Exception $e) {
	send_response("500", $e->getMessage(), "");
	die();
}
