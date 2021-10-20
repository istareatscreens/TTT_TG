<?php

namespace Game;

use Game\GameInterface;

class TicTacToe implements GameInterface
{

    private int $state;
    private string $id;
    private array $players;
    private int $playersMove;
    private int $movesLeft;
    private int $winner;
    private int $winningState;

    public function __construct()
    {
        $this->state = 0;
        $this->movesLeft = 9;
        $this->playersMove = 1; //player 1 moves first
        $this->winner = 0;
        $this->winningState = 0;
    }

    public function createGame($id, string ...$playerIds): GameInterface
    {
        $newGame = clone $this;
        $newGame->id = $id;
        $newGame->players = array();
        if (!$newGame->registerPlayers(...$playerIds)) {
            throw new \Exception("Cannot register identical players to a game in TicTacToe");
            return false;
        }
        return $newGame;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlayersMove(): int
    {
        return $this->playersMove;
    }

    public function getWinningState(): int
    {
        return $this->winningState;
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

    public function getPlayerNumber(string $playerId): int
    {
        echo "\nin get player number: " . $playerId . "\n";
        print_r($this->players);

        return $this->players[$playerId];
    }

    public function validPosition($quadrant): bool
    {
        return $quadrant > -1 && $quadrant < 9;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    private function setWinner($playerNumber): void
    {
        $this->winner = $playerNumber;
    }

    public function makeMove(string $playerId, int $quadrant): bool
    {
        $moveComplete = false;
        if (
            $this->outOfMoves()
            || !$this->validPosition($quadrant)
        ) {
            return false;
        }

        $playerNumber = $this->getPlayerNumber($playerId);
        if ($playerNumber !== $this->playersMove) {
            return false;
        }

        $gameWon = false;
        if ($this->quadrantIsEmpty($quadrant, $this->state)) {
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
        $this->movesLeft--;
        $this->playersMove = ($this->playersMove === 1) ? 2 : 1;
    }

    private function changeState(int $quadrant, int $playerNumber): void
    {
        $mask = 1 << $quadrant * 2;
        $this->state = (($this->state & ~$mask) | ($playerNumber << $quadrant * 2));
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
            $result = $this->state & $mask;
            if ($this->containsThreeOfTheSameMarks($result, $playerNumber)) {
                $this->winningState = $result;
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

    private function outOfMoves(): bool
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
