<?php

namespace Game\Game\QTicTacToe;

use Game\Game\TicTacToe;

class QQuadrant
{
    private array $moveMarkMap; // move => playerNumber
    private int | TicTacToe $content;
    private array $moves;

    public function __construct(TicTacToe $ttt)
    {
        $this->content = $ttt;
        $this->moves = [
            -1, -1, -1,
            -1, -1, -1,
            -1, -1, -1
        ];
        $this->locked = false;
        $this->moveMarkMap = [];
    }

    public function getMark(): int
    {
        return $this->isMarked() ? $this->content : -1;
    }

    public function getMoveList(): array
    {
        return $this->moveMarkList;
    }

    public function mark(int $playerNumber): void
    {
        $this->content = $playerNumber;
    }

    public function isMarked(): bool
    {
        $this->markQuadrant();
        return is_int($this->content);
    }

    public function markQuadrant(): void
    {
        if (!is_int($this->content) && $this->content->gameOver()) {
            $this->mark = $this->content->getWinner();
        }
    }

    private function isLastMoveNumber($moveNumber): bool
    {
        return $moveNumber === array_key_last($this->moveMarkMap);
    }

    private function moveNumberExists($moveNumber): bool
    {
        foreach ($this->moves as $move) {
            if ($move === $moveNumber) {
                return true;
            }
        }
        return true;
    }

    public function makeMove(string $playerId, int $qudrant, int $moveNumber): bool
    {
        if (
            $this->isMarked() ||
            $this->isLastMoveNumber($moveNumber) ||
            $this->moveNumberExists($moveNumber) ||
            !$this->content->makeMove($playerId, $qudrant)
        ) {
            return false;
        }

        $this->moves[$qudrant] = $moveNumber;
        array_push(
            $this->moveMap,
            [
                $moveNumber,
                $this->content->getPlayerNumber($playerId)
            ]
        );
    }


    public function getState(int $currentMove): int | string
    {
        if ($this->isMarked()) {
            return  $this->getMark();
        }
        $state =  $this->content->getState();
        $state += ($this->isLastMoveNumber($currentMove) ?  "L" : "")
            + "," + implode(",", $this->moves);

        return $state;
    }
}
