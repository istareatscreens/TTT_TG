<?php

namespace Game\Game\PlayerAssignment;

interface PlayersInterface
{
    public function setPlayers(array $players): bool;
    public function getPlayers(): array;
    public function getPlayerNumber(string $playerId): int;
    public function isPlayer(string $playerId): bool;
    public function getPlayerNumberByPlayerId($playerNumber);
}
