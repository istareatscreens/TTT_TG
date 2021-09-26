<?php

namespace db\table;

class Player{

    public int $game_id;
    public int $player_id;
    public int $mark;

   public function getExecuteParams(){
        return ["game_id"=>$this->game_id,
               "player_id"=>$this->player_id,
               "mark"=>$this->mark];
   }
}