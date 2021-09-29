<?php

namespace Game\Db;

include("dbConnect.php");

class Database
{

    private \PDO $db;

    public function __construct()
    {
        $this->db = connectToDatabase();
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

    public function resetDb()
    {
        $this->db->exec("DROP DATABASE tttdb");
        $sql = file_get_contents('script/db.sql');
        $this->db->exec($sql);
    }


    private function handleError(\PDOException $e, string $query)
    {
        echo "\n" . 'Database Error: ' . $e->getMessage() . "\n" .
            "with query: " . $query . "\n";
    }
}
