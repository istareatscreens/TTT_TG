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
        $hash = $this->removeNamespaceFromType($game::class);
        $this->games[$hash] = $game;
        return $this;
    }

    public function isValidPositionInGame(string $gameName, $position): bool
    {
        return $this->games[$gameName]->validPosition($position);
    }

    public function isValidGame(string $gameName)
    {
        return key_exists($gameName, $this->games);
    }

    private function removeNamespaceFromType($gameType)
    {
        return substr(strrchr($gameType, '\\'), 1);
    }

    public function createGame(string $type, $id, ...$playerId): GameInterface | false
    {
        $type = $this->removeNamespaceFromType($type);
        try {
            return $this->games[$type]->createGame($id, ...$playerId);
        } catch (\Exception $e) {
            return false;
        }
    }
}
