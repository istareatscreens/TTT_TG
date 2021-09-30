<?php

namespace Game;

class Game
{

    private int $state;
    private string $id;
    private array $players;
    private int $playersMove;
    private int $movesLeft;
    private int $winner;

    private function __construct($id)
    {
        $this->id = $id;
        $this->state = 0;
        $this->movesLeft = 9;
        $this->playersMove = 1; //player 1 moves first
        $this->winner = 0;
        $this->players = array();
    }

    public static function init($id, $playerId1, $playerId2): bool | Game
    {
        $game = new Game($id);
        if ($game->registerPlayers($playerId1, $playerId2)) {
            return $game;
        } else {
            return false;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isPlayer($playerId): bool
    {
        return key_exists($playerId, $this->players);
    }

    public function getPlayers(): array
    {
        return array_keys($this->players);
    }

    private function registerPlayers(string $playerId1, string $playerId2): bool
    {

        $this->players[$playerId1] = random_int(1, 2);
        $this->players[$playerId2] = ($this->players[$playerId1] == 1) ? 2 : 1;

        return $playerId1 !== $playerId2;
    }

    public function getMark(string $playerId): int
    {
        return $this->players[$playerId];
    }

    private function validQuadrant(int $quadrant): bool
    {
        return $quadrant > -1 && $quadrant < 9;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    private function setWinner($mark): void
    {
        $this->winner = $mark;
    }

    public function makeMove(string $playerId, int $quadrant): bool
    {
        $moveComplete = false;
        if (
            $this->outOfMoves()
            || !$this->validQuadrant($quadrant)
        ) {
            return false;
        }

        $mark = $this->getMark($playerId);
        if ($mark !== $this->playersMove) {
            return false;
        }

        $gameWon = false;
        if (!$this->quadrantIsEmpty($quadrant, $this->state)) {
            $this->changeState($quadrant, $mark);
            $gameWon = $this->wonGame($mark);
            $moveComplete = true;
            if ($moveComplete && $gameWon) {
                $this->setWinner($mark);
                $this->setMovesLeft(0);
            }
            $this->endTurn();
        };

        return $moveComplete;
    }
    private function setMovesLeft(int $moves): void
    {
        $this->movesLeft = $moves;
    }

    public function gameOver(): bool
    {
        return $this->outOfMoves() || $this->winner !== 0;
    }

    public function getState(): int
    {
        return $this->state;
    }

    private function endTurn(): void
    {
        $this->playersMove = ($this->playersMove === 1) ? 2 : 1;
    }

    private function changeState(int $quadrant, int $mark): void
    {
        $mask = 1 << $quadrant * 2;
        $this->state = (($this->state & ~$mask) | ($mark << $quadrant * 2));
    }

    private function wonGame(int $mark): bool
    {
        $wonGame = false;
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
            $result = $this->state & $mask;
            $wonGame = $this->containsThreeOfTheSameMarks($result, $mark);
        }
        return $wonGame;
    }

    private function containsThreeOfTheSameMarks(int $result, int $mark): bool
    {
        $counter = 0;
        for ($quadrant = 0; $quadrant < 9; $quadrant++) {
            $resultMark = $this->getQuadrantMark($quadrant, $result);
            $counter += $mark === $resultMark ? 1 : 0;
        }
        return $counter === 3;
    }

    public function outOfMoves(): bool
    {
        return !($this->movesLeft > 0 && $this->movesLeft < 10);
    }

    private function quadrantIsEmpty(int $quadrant, int $state): bool
    {
        return $this->getQuadrantMark($quadrant, $state) === 0;
    }

    private function getQuadrantMark(int $quadrant, int $state): int
    {
        $mask = 3 << $quadrant * 2;
        return ($mask & $state) >> $quadrant * 2;
    }
}
