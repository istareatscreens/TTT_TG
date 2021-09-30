<?php

namespace Game;


class GameFactory
{
    private array $games;

    public function __construct()
    {
        $this->games = array();
    }

    public function &addGame(GameInterface $game)
    {
        $this->games[$game::class] = $game;
        return $this;
    }

    public function createGame(string $type, $id, ...$playerId): GameInterface | false
    {
        try {
            return $this->games[$type]->createGame($id, ...$playerId);
        } catch (\Exception $e) {
            echo $e . $type;
            return false;
        }
    }
}
