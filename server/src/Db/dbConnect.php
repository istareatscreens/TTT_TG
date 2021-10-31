<?php

namespace Game\Db;

function connectToDatabase(bool $develop)
{

    $dbHost = ($develop) ? "127.0.0.1" : getenv('MYSQL_HOST');
    $dbPort = ($develop) ? "3306" : getenv('MYSQL_PORT');
    $dbUser = ($develop) ? "root" : getenv('MYSQL_USER');
    $dbPass = ($develop) ? "1234" : getenv('MYSQL_PASSWORD');
    $dbName = ($develop) ?  "tttdb" : getenv('MYSQL_DATABASE');

    try {
        $db = new \PDO('mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUser, $dbPass);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        return $db;
    } catch (\PDOException $e) {
        //echo "\n" . 'Database Error: ' . $e->getMessage() . "\n";
        sleep(4);
        return connectToDatabase($develop);
    } catch (\Exception $e) {
        exit("server failed to connect to db");
    }
}
