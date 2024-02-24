<?php

function connexionPDO()
{
    $login = 'clnadmin';
    $pass = '';
    $bd = "cln";
    $server = "localhost";
    $port = "27017";

    try {
        return new PDO("mongo:host=$server;dbname=$bd;port=$port", $login, $pass);
    } catch (PDOException $e) {
        throw $e;
    }
}

$client = new MongoDB\Client("mongodb://localhost:27017");

// $cnx = connexionPDO();

// $request = $cnx->prepare("CREATE TABLE growth_state(
//     growth_state_id INT AUTO_INCREMENT,
//     label VARCHAR(50) NOT NULL,
//     PRIMARY KEY(growth_state_id)
//  );
 
 
//  CREATE TABLE plant(
//     plant_id INT AUTO_INCREMENT,
//     label VARCHAR(50) NOT NULL,
//     latitude DECIMAL(16,14) NOT NULL,
//     longitude DECIMAL(17,14) NOT NULL,
//     growth_state_id INT,
//     leaf_amount INT,
//     PRIMARY KEY(plant_id),
//     FOREIGN KEY (growth_state_id) REFERENCES growth_state(growth_state_id)
//  );
 
//  CREATE TABLE fruit_tree(
//     fruit_tree_id INT,
//     label VARCHAR(50) NOT NULL,
//     latitude DECIMAL(16,14) NOT NULL,
//     longitude DECIMAL(17,14) NOT NULL,
//     PRIMARY KEY(fruit_tree_id)
//  );
 
 
//  INSERT INTO growth_state (label) values ('Petit soldat');
//  INSERT INTO growth_state (label) values ('Papillon');
//  INSERT INTO growth_state (label) values ('Feuilles');
 
//  ");
// $request->execute();

// echo $request->errorInfo()[2];