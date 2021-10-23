<?php

namespace Game\Game;

interface GameInterface
{
    /*
    Note: please make sure to create all reference variables after clone in createGame to
    prevent shallow copy bugs. See TicTacToe for implementation example.
    */
    public function createGame(int $id, string ...$playerIds): GameInterface;

    public function getId(): mixed;
    public function isPlayer($playerId): bool;
    public function getPlayers(): array;
    public function getPlayerNumber(string $playerId): int;
    public function getWinner();
    public function getPlayersMove(): int;
    public function makeMove(string $playerId, mixed $position): bool;
    public function gameOver(): bool;
    public function validPosition($position): bool;
    public function getState();
    public function getWinningState();
}
