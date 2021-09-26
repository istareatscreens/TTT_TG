<?php

namespace db\table;

class PlayerInfo{
   public int $player_id;
   public string $token;
   public string $client_hash;

   public function getExecuteParams(){
      return ["player_id"=>$this->player_id,
               "token"=>$this->token,
               "client_hash"=>$this->client_hash];
   }

}