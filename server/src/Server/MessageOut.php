<?php

namespace Game\Server;

class MessageOut
{

    public $gameId;
    public $playerId;

    public function __construct($playerId, $gameId = "")
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    public function createMessage(string $status, int $playerNumber = 0, $state = 0, int $winner = 0): string | false
    {
        $data = array();
        $data["status"] = $status;
        $data["playerId"] = $this->playerId;
        $data["gameId"] = $this->gameId;
        $data["state"] = $state;
        $data["playerNumber"] = $playerNumber;
        $data["winner"] = $winner;
        return json_encode($data);
    }
}
