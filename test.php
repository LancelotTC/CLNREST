<?php


include_once "fonctions.php";


// $client = connexionPDO();

// $request = $client->prepare("select growth_state_id from plant");

// $request->execute();
// echo implode($request->fetchAll(PDO::FETCH_ASSOC));
// echo $request->errorInfo()[2];


// echo json_encode($_SERVER);

echo $_ENV["DATABASE_NAME"];
$database = getenv("DATABASE_NAME");
