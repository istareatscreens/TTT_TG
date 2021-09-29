<?php

namespace Game;

class Game
{

    private $state;
    private $id;
    private $clients;
    private int $playersMove;
    private int $movesLeft;
    private int $winner;

    public function __construct($id, $client1, $client2)
    {
        $this->id = $id;
        $this->state = 0;
        $this->movesLeft = 9;
        $this->playersMove = 1; //player 1 moves first
        $this->clients = array();
        $this->registerClient($client1);
        $this->registerClient($client2);
        $this->winner = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPlayer($playerId)
    {
        return $playerId === $this->clients[0][0] || $playerId === $this->clients[1][0];
    }

    public function getPlayers()
    {
        return array($this->clients[0][0], $this->clients[1][0]);
    }

    public function registerClient($client): bool
    {
        if (count($this->clients) > 2 && $client !== $this->clients[0]) {
            return false;
        } elseif (count($this->clients) == 0) {
            array_push($this->clients, array($client, random_int(1, 2)));
            return true;
        } else {
            array_push($this->clients, array($client, $this->clients[0][1] == 1 ? 2 : 1));
            return true;
        }
        return false;
    }

    public function gameReady(): bool
    {
        return count($this->clients) === 2;
    }

    private function getMark($client): int
    {
        $mark = -1;
        if ($client === $this->clients[0][0]) {
            $mark === $this->clients[0][1];
        } else if ($client === $this->clients[1][0]) {
            $mark === $this->clients[1][1];
        }
        return $mark;
    }

    private function validQuadrant($quadrant): bool
    {
        return $quadrant < 9;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    private function setWinner($mark): void
    {
        $this->winner = $mark;
    }

    public function makeMove($client, $quadrant): bool
    {
        $moveComplete = false;
        if (
            $this->outOfMoves()
            || !$this->gameReady()
            || !$this->validQuadrant($quadrant)
        ) {
            return false;
        }

        $mark = $this->getMark($client);
        if ($mark === -1 || $mark !== $this->playersMove) {
            return false;
        }

        $gameWon = false;
        if (!$this->quadrantIsEmpty($quadrant, $this->state)) {
            $this->changeState($quadrant, $mark);
            $gameWon = $this->wonGame($mark);
            $moveComplete = true;
            $this->endTurn();
        };

        if ($moveComplete && $gameWon) {
            $this->setWinner($mark);
            $this->setMovesLeft(0);
        }

        return $moveComplete;
    }
    private function setMovesLeft($moves)
    {
        $this->movesLeft;
    }

    public function gameOver(): bool
    {
        return $this->outOfMoves() || $this->winner !== 0;
    }

    public function getState()
    {
        return $this->state;
    }

    private function endTurn(): void
    {
        $this->playersMove = ($this->playersMove === 1) ? 2 : 1;
    }

    private function changeState($quadrant, $mark): void
    {
        $mask = 1 << $quadrant * 2;
        $this->state = (($this->state & ~$mask) | ($mark << $quadrant * 2));
    }

    private function wonGame($mark): bool
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

    private function containsThreeOfTheSameMarks($result, $mark): bool
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
        return $this->movesLeft < 1;
    }

    private function quadrantIsEmpty($quadrant, $state): bool
    {
        return $this->getQuadrantMark($quadrant, $state) === 0;
    }

    private function getQuadrantMark($quadrant, $state): int
    {
        $mask = 3 << $quadrant * 2;
        return ($mask & $state) >> $quadrant * 2;
    }
}
