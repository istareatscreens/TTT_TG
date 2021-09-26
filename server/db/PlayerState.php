<?php

namespace db;

class PlayerState{

    private $db;

    public function __construct(Database $db){
        $this->db = $db;
    }


    public function registerPlayer(table\PlayerInfo $player): void{
        $query = "INSERT INTO player_info(token, client_hash) ".
                    "VALUES(:token, :client_hash)";
        $this->db->query(
            $query,
            $player->getExecuteParams()
        );
    }

    public function getPlayerInfo(string $token){
        $query = "SELECT * FROM player_info WHERE token = UUID_TO_BIN(:token)";
        return $this->db->select(
            $query,
            ["token"=>$token]
        );
    }

    public function getPlayer(table\PlayerInfo $player){
        $query = "SELECT * FROM player WHERE player_id = :player_id";
        return $this->db->select(
            $query,
            $player->getExecuteParams()
        );
    }

}