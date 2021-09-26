<?php
namespace db;

require_once "./db/dbConnect.php";

class Database{

    private $db;

    public function __construct(){
        $this->dbConnect = \db\connectToDatabase();
    }

    public function select(string $sqlQuery, array $params) {
        try{
            $query = $this->db->query($sqlQuery);
            $query->execute($params);
            $result = $query->fetch();
            return $result;
        }catch(\PDOException $e){
            echo "\n".'Database Error: ' . $e->getMessage()."\n";
            return null;
        }
    }

    public function query(string $sqlQuery, array $params): void{
        try{
            $query = $this->db->query($sqlQuery);
            $query->execute($params);
        }catch(\PDOException $e){
            echo "\n".'Database Error: ' . $e->getMessage()."\n";
        }
    }

}
