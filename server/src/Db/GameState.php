<?php

namespace Game\Db;

class GameState
{

    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createGame(string $gameId, string $playerId1, string $playerId2): bool
    {
        $query = "SELECT player_id FROM player WHERE token = UUID_TO_BIN(:player1) OR token = UUID_TO_BIN(:player2)";
        $results = $this->db->selectAll($query, [
            "player1" => $playerId1,
            "player2" => $playerId2
        ]);

        if (is_null($results) || count($results) === 0) {
            return false;
        }

        $query = "INSERT INTO game(token, player1, player2) " .
            "VALUES(UUID_TO_BIN(:token), :player1, :player2)";
        $this->db->query(
            $query,
            [
                "token" => $gameId,
                "player1" => isset($results[0]["player_id"]) ? $results[0]["player_id"] :  \PDO::PARAM_NULL,
                "player2" => isset($results[1]["player_id"]) ? $results[1]["player_id"] :  \PDO::PARAM_NULL
            ]
        );
        return true;
    }

    public function getGames(string $playerId)
    {
        $query = "SELECT player_id FROM player WHERE token = UUID_TO_BIN(:token)";
        $result = $this->db->select(
            $query,
            ["token" => $playerId]
        );
        $query = "SELECT * FROM game WHERE player1 = :player_id || player2 = :player_id";
        return $this->db->selectAll(
            $query,
            [":player1" => $result]
        );
    }

    public function getPlayers(string $gameId)
    {
        $query = "SELECT * FROM game WHERE token = :token";
        return $this->db->select($query, ["token" => $gameId]);
    }

    public function deleteGame(string $gameId)
    {
        $query = "DELETE FROM game WHERE token = UUID_TO_BIN(:token)";
        $this->db->query($query, ["token" => $gameId]);
    }
}
