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
        $hash = $this->removeNamespaceFromType($game);
        $this->games[$hash] = $game;
        return $this;
    }

    private function removeNamespaceFromType($gameType)
    {
        return substr(strrchr($gameType::class, '\\'), 1);
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
