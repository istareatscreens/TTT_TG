<?php

namespace Game\Game\PlayerAssignment;

class RandomAssign extends AbstractPlayers
{
    public function __construct()
    {
        parent::__construct();
    }

    // expects array [player1, player2]
    public function setPlayers(array $players): bool
    {
        $this->registerPlayers(...$players);
        return !$this->checkIfPlayersAreDuplicate(...$players);
    }

    private function checkIfPlayersAreDuplicate(string $playerId1, string $playerId2)
    {
        return $playerId1 === $playerId2;
    }

    private function registerPlayers(string $playerId1, string $playerId2): bool
    {

        $this->players[$playerId1] = random_int(1, 2);
        $this->players[$playerId2] = ($this->players[$playerId1] == 1) ? 2 : 1;
        return $playerId1 !== $playerId2;
    }
}
