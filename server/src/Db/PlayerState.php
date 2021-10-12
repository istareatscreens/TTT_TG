<?php

namespace Game\Db;

class PlayerState
{

    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function savePlayer(string $playerId, string $hash): bool
    {
        $query = "INSERT INTO player (player_token, client_hash) VALUES (UUID_TO_BIN(:token), :client_hash)";
        return $this->db->query(
            $query,
            array(
                "token" => $playerId,
                "client_hash" => $hash
            )
        );
    }

    public function deletePlayer(string $playerId): bool
    {
        $query = "DELETE FROM game WHERE player_token = UUID_TO_BIN(:token)";
        return $this->db->query($query, ["token" => $playerId]);
    }

    public function playerExistsByToken(string $playerId): bool
    {
        $result = $this->getPlayerDataFromToken($playerId);
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

    public function getPlayerDataFromToken(string $playerId)
    {
        $query = "SELECT player_id, BIN_TO_UUID(player_token), client_hash FROM player " .
            "WHERE player_token = UUID_TO_BIN(:token)";
        return $this->db->select(
            $query,
            ["token" => $playerId]
        );
    }

    public function getPlayerFromPlayer_Id(string $player_id)
    {
        $query = "SELECT player_id, BIN_TO_UUID(player_token), client_hash FROM player " .
            "WHERE player_id = :player_id";
        return $this->db->select(
            $query,
            ["player_id" => $player_id]
        );
    }

    public function updateClientHash(string $playerId, string $hash = ""): bool
    {
        $query = "UPDATE player " .
            "SET client_hash = :client_hash " .
            "WHERE player_token = UUID_TO_BIN(:token)";
        return $this->db->query(
            $query,
            [
                "token" => $playerId,
                "client_hash" => ($hash === "") ? NULL : $hash
            ]
        );
    }
}
