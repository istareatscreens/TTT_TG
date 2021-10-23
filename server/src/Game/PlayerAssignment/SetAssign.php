<?php

namespace Game\Game\PlayerAssignment;

class SetAssign extends AbstractPlayers
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setPlayers(array $players): bool
    {
        [$playerId1, $playerId2] = $players;
        $this->players =  [$playerId1 => 1, $playerId2 => 2];
        return !$this->checkIfPlayersAreDuplicate(...$this->getPlayers());
    }

    private function checkIfPlayersAreDuplicate(string $playerId1, string $playerId2)
    {
        return $playerId1 === $playerId2;
    }

    public function swapPlayerNumbers()
    {
        [$player1, $player2] = $this->getPlayers();
        $this->players = [
            $player1 => $this->getPlayerNumber($player2),
            $player2 => $this->getPlayerNumber($player1)
        ];
    }
}
