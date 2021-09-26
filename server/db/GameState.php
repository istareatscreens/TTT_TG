<?php

namespace db;

class GameState{

    private $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    public function createGame(table\GameStatus $gameStatus){
        $query = "INSERT INTO game_status(token, turn, state, moves_left, complete, winner) ".
                    "VALUES(:token, :turn, :state, :moves_left, :complete, :winner)";
        $this->db->query(
            $query,
            $gameStatus->getExecuteParams()
        );
    }

    public function addPlayers(
        table\PlayerInfo $player1,
        table\PlayerInfo $player2,
        table\GameStatus $game){
        $query = "INSERT INTO player(game_id, player1, player2) ".
                    "VALUES(:game_id, :player1, :player2)";
        $this->db->query(
            $query,
            ["player1"=>$player1->player_id,
            "player2"=>$player2->player_id,
            "game_id"=>$game->game_id]
        );
    }

    public function registerPlayer(table\Player $player){
        $query = "INSERT INTO player(game_id, player_id, mark) ".
                    "VALUES(:game_id, :player_id, :mark)";
        $this->db->query(
            $query,
            ["player_id"=>$player->player_id,
            "mark"=>$player->mark,
            "game_id"=>$player->game_id]
        );
    }

    public function updateGame(table\GameStatus $game_status){
        $query = "UPDATE player ".
                    "SET turn=:turn, state=:state, moves_left:=moves_left, complete=:complete, winner=:winner ".
                    "WHERE game_id=:game_id";
        $this->db->query(
            $query,
            $game_status->getExecuteParams()
        );
    }

    public function getGameInfo(string $token){
        $query = "SELECT * FROM game_status WHERE token = UUID_TO_BIN($token)";
        $result = $this->db->select(
            $query,
            ["token"=>$token]
        );
        return $result;
    }

}