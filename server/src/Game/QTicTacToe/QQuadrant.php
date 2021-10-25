<?php

namespace Game\Game\QTicTacToe;

use Game\Game\TicTacToe;

class QQuadrant
{
    private array $moveMarkMap; // move => playerNumber
    private int | TicTacToe $content;
    private array $moves;
    private int $id;

    public function __construct(TicTacToe $ttt, int $id)
    {
        $this->content = $ttt;
        $this->moves = [
            -1, -1, -1,
            -1, -1, -1,
            -1, -1, -1
        ];
        $this->locked = false;
        $this->moveMarkMap = [];
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMark(): int
    {
        return $this->isMarked() ? $this->content : -1;
    }

    public function getMoveList(): array
    {
        return $this->moveMarkMap;
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

    public function setPlayersMove(int $playerNumber): void
    {
        if (!$this->isMarked()) {
            $this->content->setPlayersMove($playerNumber);
        }
    }

    public function markQuadrant(): void
    {
        if (!is_int($this->content) && $this->content->gameOver()) {
            $this->mark = $this->content->getWinner();
        }
    }

    private function isLastMoveNumber($moveNumber): bool
    {
        return count($this->moveMarkMap) !== 0
            && $moveNumber === $this->getLastMove();
    }

    private function getLastMove()
    {
        return $this->moveMarkMap[array_key_last($this->moveMarkMap)][0];
    }

    private function moveNumberExists($moveNumber): bool
    {
        foreach ($this->moves as $move) {
            if ($move === $moveNumber) {
                return true;
            }
        }
        return false;
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
            $this->moveMarkMap,
            [
                $moveNumber,
                $this->content->getPlayerNumber($playerId)
            ]
        );
        return true;
    }


    public function getState(int $currentMove, bool $lock): int | string
    {
        if ($this->isMarked()) {
            return  $this->getMark();
        }
        $state =
            ($this->isLastMoveNumber($currentMove) && $lock ?  "L" : "")
            . $this->content->getState()
            . "," . implode(",", $this->moves);

        return $state;
    }
}
