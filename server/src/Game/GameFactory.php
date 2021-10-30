<?php

namespace Game\Game;

use Reflection;

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

    public function isValidPositionInGame(string $gameName, $position): bool
    {
        return $this->games[$gameName]->validPosition($position);
    }

    public function isValidGame(string $gameName): bool
    {
        try {
            return key_exists($gameName, $this->games);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function removeNamespaceFromType($game)
    {
        return (new \ReflectionClass($game))->getShortName();
    }

    public function createGame(string $type, $id, ...$playerId): GameInterface | false
    {
        try {
            return $this->isValidGame($type) ?
                $this->games[$type]->createGame($id, ...$playerId) :
                false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
