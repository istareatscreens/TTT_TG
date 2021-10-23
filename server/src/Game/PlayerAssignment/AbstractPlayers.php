<?php

namespace Game\Game\PlayerAssignment;

use PDO;

abstract class AbstractPlayers implements PlayersInterface
{
    protected array $players;

    public function __construct()
    {
        $this->players = [];
    }

    public function getPlayers(): array
    {
        return array_keys($this->players);
    }

    public function getPlayerNumber(string $playerId): int
    {
        return $this->players[$playerId];
    }

    public function getPlayerNumberByPlayerId($playerNumber)
    {
        $players = $this->getPlayers();
        foreach ($players as $player) {
            if ($this->getPlayerNumber($player) === $playerNumber) {
                return $player;
            }
        }
        return -1;
    }

    public function isPlayer($playerId): bool
    {
        return key_exists($playerId, $this->players);
    }
}
