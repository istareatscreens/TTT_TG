<?php

namespace db\table;

class GameStatus {
    public int $game_id;
    public string $token;
    public int $turn;
    public int $movesLeft; 
    public bool $complete;
    public int $winner;

   public function getExecuteParams(){
      return ["game_id"=>$this->game_id,
               "token"=>$this->token,
               "turn"=>$this->turn,
               "movesLeft"=>$this->movesLeft,
               "complete"=>$this->complete,
               "winner"=>$this->winner];
    }
}