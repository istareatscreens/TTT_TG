<?php

namespace Game\Db;

include("dbConnect.php");

class Database
{

    private \PDO $db;
    private bool $develop;

    public function __construct(bool $develop)
    {
        $this->db = connectToDatabase($develop);
        $this->develop = $develop;
    }

    public function select(string $sqlQuery, array $params)
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
            return $query->fetch();
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
            return null;
        }
    }

    public function query(string $sqlQuery, array $params): bool
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
            return true;
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
            return false;
        }
    }

    public function selectAll($sqlQuery, $params): array | NULL
    {
        try {
            $query = $this->db->prepare($sqlQuery);
            $query->execute($params);
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->handleError($e, $sqlQuery);
            return null;
        }
    }

    public function resetDb()
    {
        $this->db = connectToDatabase($this->develop);
        $truncate = "TRUNCATE TABLE ";
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec($truncate . "player");
        $this->db->exec($truncate . "game");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
    }


    private function handleError(\PDOException $e, string $query)
    {
        echo "\n" . 'Database Error: ' . $e->getMessage() . "\n" .
            "with query: " . $query . "\n";
    }
}
