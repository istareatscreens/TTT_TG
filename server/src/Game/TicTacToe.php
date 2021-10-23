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

    public function validPosition($quadrant): bool
    {
        return $quadrant > -1 && $quadrant < 9;
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
        $winningMasks = array(
            0b110000110000110000,
            0b001100001100001100,
            0b000011000011000011,
            0b110000001100000011,
            0b000011001100110000,
            0b111111000000000000,
            0b000000111111000000,
            0b000000000000111111
        );
        foreach ($winningMasks as &$mask) {
            $result = $this->getState() & $mask;
            if ($this->containsThreeOfTheSameMarks($result, $playerNumber)) {
                $this->setWinningState($result);
                return true;
            }
        }
        return false;
    }

    private function containsThreeOfTheSameMarks(int $result, int $playerNumber): bool
    {
        $counter = 0;
        for ($quadrant = 0; $quadrant < 9; $quadrant++) {
            $resultMark = $this->getQuadrantMark($quadrant, $result);
            $counter += $playerNumber === $resultMark ? 1 : 0;
        }
        return $counter === 3;
    }
}
