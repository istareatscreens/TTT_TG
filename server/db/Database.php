<?php

namespace Db;

use Db;

require_once "dbConnect.php";

class Database
{

    private \PDO $db;

    public function __construct()
    {
        $this->db = \Db\connectToDatabase();
    }

    public function select(string $sqlQuery, array $params)
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
            $result = $query->fetch();
            return $result;
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
            return null;
        }
    }

    public function query(string $sqlQuery, array $params): void
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
        }
    }

    public function selectAll($sqlQuery, $params)
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
            $result = $query->fetch();
            return $result;
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
            return null;
        }
    }

    private function handleError(\PDOException $e, string $query)
    {
        echo "\n" . 'Database Error: ' . $e->getMessage() . "\n" .
            "with query: " . $query . "\n";
    }
}
