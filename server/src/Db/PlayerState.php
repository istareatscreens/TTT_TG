<?php

namespace Game\Db;

class PlayerState
{

    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function savePlayer(string $playerId, string $hash): void
    {
        $query = "INSERT INTO tttdb.player (token, client_hash) VALUES (UUID_TO_BIN(:token), :client_hash)";
        $this->db->query(
            $query,
            array(
                "token" => $playerId,
                "client_hash" => $hash
            )
        );
    }

    public function playerExistsByToken(string $token): bool
    {
        $result = $this->getPlayerDataFromToken($token);
        return !is_null($result) && $result;
    }

    public function playerExistsByHash(string $hash)
    {
        $result = $this->getPlayerDataFromHash($hash);
        return !is_null($result) && $result && $result;
    }

    public function getPlayerDataFromHash(string $hash)
    {
        $query = "SELECT * FROM player WHERE client_hash = :client_hash";
        return $this->db->select(
            $query,
            ["client_hash" => $hash]
        );
    }

    public function getPlayerDataFromToken(string $token)
    {
        $query = "SELECT player_id, BIN_TO_UUID(token), client_hash FROM player " .
            "WHERE token = UUID_TO_BIN(:token)";
        return $this->db->select(
            $query,
            ["token" => $token]
        );
    }

    public function getPlayerFromPlayer_Id(string $player_id)
    {
        $query = "SELECT player_id, BIN_TO_UUID(token), client_hash FROM player " .
            "WHERE player_id = :player_id";
        return $this->db->select(
            $query,
            ["player_id" => $player_id]
        );
    }

    public function updateClientHash(string $token, string $hash = "")
    {
        $query = "UPDATE player " .
            "SET client_hash = :client_hash " .
            "WHERE token = UUID_TO_BIN(:token)";
        $this->db->query(
            $query,
            [
                "token" => $token,
                "client_hash" => ($hash === "") ? \PDO::PARAM_NULL : $hash
            ]
        );
    }
}
