<?php

function connexionPDO()
{
    $login = 'clnadmin';
    $pass = '';
    $bd = "cln";
    $server = "34.172.4.138";
    $port = "3306";

    try {
        return new PDO("mysql:host=$server;dbname=$bd;port=$port", $login, $pass);
    } catch (PDOException $e) {
        throw $e;
    }
}
