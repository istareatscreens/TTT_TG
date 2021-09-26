<?php

namespace db\table;

class Game{
   public int $game_id;
   public int $player1;
   public int $player2;

   public function getExecuteParams(){
      return ["game_id"=>$this->game_id,
               "player1"=>$this->player1,
               "player2"=>$this->player2];
   }
    
}