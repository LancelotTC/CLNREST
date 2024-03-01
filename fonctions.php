<?php

function connexionPDO()
{
    $config = parse_ini_file("config.ini");
    $login = $config["login"];
    $pass = $config["pass"];
    $bd = $config["bd"];
    $server = $config["server"];
    $port = "3306";

    try {
        return new PDO("mysql:host=$server;dbname=$bd;port=$port", $login, $pass);
    } catch (PDOException $e) {
        throw $e;
    }
}

$cnx = connexionPDO();

$request = $cnx->prepare("select * from plant");
$request->execute();

echo 200;




