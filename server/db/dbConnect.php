<?php

namespace db;

function connectToDatabase() {
    $dbHost=getenv('MYSQL_HOST');
    $dbPort=getenv('MYSQL_PORT');
    $dbUser=getenv('MYSQL_USER');
    $dbPass=getenv('MYSQL_PASSWORD');
    $dbName=getenv('MYSQL_DATABASE');
    try {
        $db = new \PDO('mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUser, $dbPass);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        echo "\n"."Connected to Database Successfully"."\n";
        return $db;
    } catch( \PDOException $e) {
        echo "\n".'Database Error: ' . $e->getMessage()."\n";
    }
}
