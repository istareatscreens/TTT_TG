<?php

namespace Game\Game;

use Game\Game\AbstractTicTacToe;
use Game\Game\PlayerAssignment\RandomAssign;
use Game\Game\PlayerAssignment\PlayersInterface;

class TicTacToe extends AbstractTicTacToe
{

    public function __construct(PlayersInterface $players = null)
    {
        parent::__construct(isset($players) ? $players : new RandomAssign());
    }

    public function makeMove(string $playerId, mixed $quadrant): bool
    {
        $moveComplete = false;
        if (
            $this->outOfMoves()
            || !$this->validPosition($quadrant)
        ) {
            return false;
        }

        $playerNumber = $this->getPlayerNumber($playerId);
        if ($playerNumber !== $this->getPlayersMove()) {
            return false;
        }

        $gameWon = false;
        if ($this->quadrantIsEmpty($quadrant, $this->getState())) {
            $this->changeState($quadrant, $playerNumber);
            $gameWon = $this->wonGame($playerNumber);
            $moveComplete = true;
            if ($moveComplete && $gameWon) {
                $this->setWinner($playerNumber);
                $this->setMovesLeft(0);
            }
            $this->endTurn();
        };

        return $moveComplete;
    }

    private function endTurn(): void
    {
        $this->setMovesLeft($this->getMovesLeft() - 1);
        $this->setPlayersMove($this->getPlayersMove() === 1 ? 2 : 1);
    }

    private function wonGame(int $playerNumber): bool
    {
        foreach (parent::$winningMasks as &$mask) {
            $result = $this->getState() & $mask;
            if ($this->containsThreeOfTheSameMarks($result, $playerNumber)) {
                $this->setWinningState($result);
                return true;
            }
        }
        return false;
    }
}
