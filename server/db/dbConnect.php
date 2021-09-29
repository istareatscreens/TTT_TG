<?php

namespace Db;

function connectToDatabase()
{
    $debug = getenv("MYSQL_DEBUG") ? false : true;

    $dbHost = ($debug) ? "127.0.0.1" : getenv('MYSQL_HOST');
    $dbPort = ($debug) ? "3306" : getenv('MYSQL_PORT');
    $dbUser = ($debug) ? "root" : getenv('MYSQL_USER');
    $dbPass = ($debug) ? "1234" : getenv('MYSQL_PASSWORD');
    $dbName = ($debug) ?  "tttdb" : getenv('MYSQL_DATABASE');
    try {
        $db = new \PDO('mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUser, $dbPass);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        echo "\n" . "Connected to Database Successfully" . "\n";
        return $db;
    } catch (\PDOException $e) {
        echo "\n" . 'Database Error: ' . $e->getMessage() . "\n";
    }
}
