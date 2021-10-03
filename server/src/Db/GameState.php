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
        $query = "SELECT player_id FROM player WHERE player_token = UUID_TO_BIN(:player1) OR player_token = UUID_TO_BIN(:player2)";
        $results = $this->db->selectAll($query, [
            "player1" => $playerId1,
            "player2" => $playerId2
        ]);

        if (is_null($results) || count($results) === 0) {
            return false;
        }

        $query = "INSERT INTO game(game_token, player1, player2) " .
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

    public function getGame($gameId)
    {
        $query = "SELECT * FROM game WHERE UUID_TO_BIN(:game_token) = game_token";
        $result = $this->db->select($query, ["game_token" => $gameId]);
        return $result;
    }

    public function getGamesAndPlayers(string $playerId)
    {
        $query = "SELECT player_id FROM player WHERE player_token = UUID_TO_BIN(:token)";
        $result = $this->db->select(
            $query,
            ["token" => $playerId]
        );
        $query = "SELECT * FROM game WHERE player1 = :player_id || player2 = :player_id";
        $result = $this->db->selectAll(
            $query,
            [":player1" => $result]
        );
    }

    public function getAllGameIdsPlayerIdsAndClientHashesFromPlayerID($playerId)
    {

        $query =
            "WITH active_games  AS (SELECT DISTINCT game_id FROM "
            . " (player AS p INNER JOIN game AS g "
            . "ON p.player_id = g.player1 OR p.player_id = g.player2 "
            . ") WHERE p.player_token = UUID_TO_BIN(:player_token)), "
            . "game_player_union AS "
            . "(SELECT * FROM "
            . " (player AS p INNER JOIN game AS g "
            . " ON p.player_id = g.player1 "
            . " ) "
            . " UNION ALL "
            . " SELECT * FROM "
            . " (player AS p INNER JOIN game AS g "
            . " ON p.player_id = g.player2 "
            . " )) "
            . "SELECT DISTINCT BIN_TO_UUID(player_token), client_hash, BIN_TO_UUID(game_token) FROM "
            . "game_player_union "
            . "INNER JOIN active_games "
            . "WHERE game_player_union.game_id = active_games.game_id "
            . "AND player_token != UUID_TO_BIN(:player_token)";
        $results = $this->db->selectAll(
            $query,
            ["player_token" => $playerId]
        );

        if (count($results) === 0) {
            return NULL;
        }

        $games = []; // [$game_token] => [player_id, client_hash]
        foreach ($results as $result) {
            $gameId = $result["BIN_TO_UUID(game_token)"];
            if (!key_exists($gameId, $games)) {
                $games[$gameId] = [];
            }
            array_push($games[$gameId], [$result["BIN_TO_UUID(player_token)"], $result["client_hash"]]);
        }
        return $games;
    }

    public function getPlayers(string $gameId)
    {
        $query = "SELECT * FROM game WHERE game_token = :token";
        return $this->db->select($query, ["token" => $gameId]);
    }

    public function deleteGame(string $gameId)
    {
        $query = "DELETE FROM game WHERE game_token = UUID_TO_BIN(:token)";
        $this->db->query($query, ["token" => $gameId]);
    }
}
