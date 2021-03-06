<?php

namespace Game\Game;

use Game\Game\GameInterface;
use Game\Game\PlayerAssignment\PlayersInterface;

abstract class AbstractTicTacToe implements GameInterface
{
    protected static $winningMasks = array(
        0b110000110000110000,
        0b001100001100001100,
        0b000011000011000011,
        0b110000001100000011,
        0b000011001100110000,
        0b111111000000000000,
        0b000000111111000000,
        0b000000000000111111
    );

    private int $state;
    private $id;
    private PlayersInterface $players;
    private int $playersMove;
    private int $movesLeft;
    private int $winner;
    private int $winningState;

    public function __construct(PlayersInterface $players)
    {
        $this->state = 0;
        $this->movesLeft = 9;
        $this->playersMove = 1; //player 1 moves first
        $this->winner = 0;
        $this->winningState = 0;
        $this->players = $players;
    }

    public function createGame($id, string ...$playerIds): GameInterface
    {
        $newGame = clone $this;
        $newGame->players = clone $this->players;
        $newGame->id = $id;
        if (!$newGame->players->setPlayers([...$playerIds])) {
            throw new \Exception("Cannot register identical players to a game in TicTacToe");
        }
        return $newGame;
    }

    protected function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getPlayersMove(): int
    {
        return $this->playersMove;
    }


    /* 
    hacky solution to turn sync problem in QTicTacToe should not be included in interface
    */
    public function setPlayersMove(int $playersMove): void
    {
        $this->playersMove = $playersMove;
    }

    public function getWinningState(): int
    {
        return $this->winningState;
    }

    public function isPlayer($playerId): bool
    {
        return $this->players->isPlayer($playerId);
    }

    public function getPlayers(): array
    {
        return $this->players->getPlayers();
    }

    public function getPlayerNumber(string $playerId): int
    {
        return $this->players->getPlayerNumber($playerId);
    }

    public function validPosition($quadrant): bool
    {
        return $quadrant > -1 && $quadrant < 9;
    }

    public function getWinner(): int
    {
        return $this->winner;
    }

    protected function setWinner($playerNumber): void
    {
        $this->winner = $playerNumber;
    }

    protected function setWinningState($winningState)
    {
        $this->winningState = $winningState;
    }

    protected function isPlayersMove($playerNumber)
    {
        return ($playerNumber === $this->playersMove);
    }

    protected function getMovesLeft(): int
    {
        return $this->movesLeft;
    }

    protected function setMovesLeft(int $moves): void
    {
        $this->movesLeft = $moves;
    }

    public function gameOver(): bool
    {
        return $this->outOfMoves() || $this->winner !== 0;
    }

    public function getState(): mixed
    {
        return $this->state;
    }

    protected function setState($state): void
    {
        $this->state = $state;
    }

    protected function changeState(int $quadrant, int $playerNumber): void
    {
        $mask = 1 << $quadrant * 2;
        $this->state = (($this->state & ~$mask) | ($playerNumber << $quadrant * 2));
    }

    protected function containsThreeOfTheSameMarks(int $result, int $playerNumber): bool
    {
        $counter = 0;
        for ($quadrant = 0; $quadrant < 9; $quadrant++) {
            $resultMark = $this->getQuadrantMark($quadrant, $result);
            $counter += $playerNumber === $resultMark ? 1 : 0;
        }
        return $counter === 3;
    }

    protected function outOfMoves(): bool
    {
        return !($this->movesLeft > 0 && $this->movesLeft < 10);
    }


    public function getPlayerNumberByPlayerId($playerNumber): string
    {
        return $this->players->getPlayerNumberByPlayerId($playerNumber);
    }

    protected function quadrantIsEmpty(int $quadrant, int $state): bool
    {
        return $this->getQuadrantMark($quadrant, $state) === 0;
    }

    protected function getQuadrantMark(int $quadrant, int $state): int
    {
        $mask = 3 << $quadrant * 2;
        return ($mask & $state) >> $quadrant * 2;
    }
}
