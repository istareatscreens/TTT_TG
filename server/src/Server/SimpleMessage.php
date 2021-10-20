<?php

namespace Game\Server;

use Game\GameInterface;

class MessageOut
{

    private GameInterface|null $game;


    public function __construct(GameInterface|null $game = null)
    {
        $this->$game = $game;
    }

    private function handleWhenGameIsNull($callback, ...$args): mixed
    {
        return (!isset($this->game)) ? $callback(...$args) : 0;
    }

    public function createMessage(string $status, $playerId = ""): string
    {
        $data = array();
        $data["status"] = $status;
        $data["gameId"] = $this->handleWhenGameIsNull(function () {
            return $this->game->getId();
        });
        $data["state"] = $this->handleWhenGameIsNull(function () {
            return $this->game->getState();
        });
        $data["playerNumber"] = $this->handleWhenGameIsNull(function ($playerId) {
            return $this->game->getPlayerNumber($playerId);
        }, $playerId);
        $data["turn"] = $this->handleWhenGameIsNull(function () {
            return $this->game->getPlayersMove();
        });
        $data["winner"] = $this->handleWhenGameIsNull(function () {
            return $this->game->getWinner();
        });
        $data["gameOverState"] = $this->handleWhenGameIsNull(function () {
            return $this->game->getWinningState();
        });
        return json_encode($data);
    }
}
