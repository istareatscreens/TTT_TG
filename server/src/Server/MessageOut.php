<?php

namespace Game\Server;

class MessageOut
{

    public $gameId;
    public $playerId;

    public function __construct($gameId = "")
    {
        $this->gameId = $gameId;
    }

    public function createMessage(string $status, int $playerNumber = 0, $state = 0, int $winner = 0, int $gameOverState = 0): string | false
    {
        $data = array();
        $data["status"] = $status;
        $data["gameId"] = $this->gameId;
        $data["state"] = $state;
        $data["playerNumber"] = $playerNumber;
        $data["winner"] = $winner;
        $data["gameOverState"] = $gameOverState;
        return json_encode($data);
    }
}
