<?php

namespace Game\Server\Messages;

use Game\Server\Messages\Message;
use Game\GameInterface;

class GameMessage extends Message
{
    private GameInterface $game;

    public function __construct(GameInterface $game, $playerId)
    {
        $this->game = $game;
        $this->message["gameId"] = $game->getId();
        $this->message["state"] = $game->getState();
        $this->message["playerNumber"] = $game->getPlayerNumber($playerId);
        $this->message["turn"] = $game->getPlayersMove();
        $this->message["winner"] =  $game->getWinner();
        $this->message["gameOverState"] = $game->getWinningState();
    }
}
