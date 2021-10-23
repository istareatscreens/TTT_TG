<?php

namespace Game\Game\QTicTacToe;

use Game\Game\AbstractTicTacToe;
use Game\Game\GameInterface;
use Game\Game\PlayerAssignment\RandomAssign;
use Game\Game\PlayerAssignment\SetAssign;
use Game\Game\TicTacToe;
use Game\Game\QTicTacToe\QQuadrant;

class QTicTacToe extends AbstractTicTacToe
{
    private array $quadrants;
    private int $moves;
    private int $moveNumber;
    private array $edgeList;
    private int $previousQuadrant;
    private array $graph; // Adjacency list

    public function __construct()
    {
        parent::__construct(new RandomAssign());
        $this->graph = array_fill(0, 9, []);
        $this->edgeList = [];
        $this->quadrants = [];
        $this->moves = 2;
        $this->moveNumber = 0;
    }

    public function createGame($id, string ...$playerIds): GameInterface
    {
        [$playerId1, $playerId2] = $playerIds;

        $newGame = parent::createGame($id, ...$playerIds);
        $number = $newGame->getPlayerNumber($playerId1);
        $players = ($number === 1) ? [$playerId1, $playerId2] : [$playerId2, $playerId1];
        $ttt = new TicTacToe(new SetAssign());
        for ($i = 0; $i < 9; $i++) {
            $newGame->quadrants[$i] = new QQuadrant(
                $ttt->createGame($i, ...$players)
            );
        }
        return $newGame;
    }

    /*
    protected function outOfMoves(): bool
    {
        return $this->getMovesLeft() === 0;
    }
    */

    public function validPosition($quadrant): bool
    {
        return false;
    }

    private function finishMove(): void
    {
        if (!--$this->moves) {
            $this->endTurn();
        }
    }

    private function endTurn()
    {
        $this->moves = 2;
        $this->previousQuadrant = -1;
        $this->moveNumber++;
        $this->setPlayersMove(($this->players === 1) ? 2 : 1);
    }

    private function updateGraph($quadrant): void
    {
        // not last move for player
        if ($this->moves !== 1) {
            return;
        }
        $this->addToGraph($quadrant, $this->previousQuadrant);
    }

    private function collapseState($quadrant): void
    {
        $getConnectedQuadrant = function ($move, $quadrant) {
            [$quadrant1, $quadrant2] = $this->edgeList[$move];
            return ($quadrant1 === $quadrant) ? $quadrant2 : $quadrant1;
        };

        //$marks = array_fill(0, count($this->quadrants), -1);
        $visited = array_fill(0, $this->moveNumber + 1, false);

        // handle initial quadrant
        $moveList = $this->quadrants[$quadrant]->getMoveList();
        [$move, $mark] = array_pop($moveList);
        //$this->quadrants[$quadrant]->mark($mark);
        $this->markQuadrant($quadrant, $mark, $move);
        //$marks[$quadrant] = $mark;
        $visited[$move] = true;

        $stack = $moveList;
        while (count($stack)) {
            [$move, $mark] = array_pop($stack);
            if ($visited[$move]) {
                continue;
            }
            $quadrant = $getConnectedQuadrant($move, $quadrant);
            $visited[$move] = true;
            //$marks[$quadrant] = $mark;
            $this->markQuadrant($quadrant, $mark, $move);
            // $this->quadrants[$quadrant]->mark($mark);
            echo "\nUpdated Quadrant: " . $quadrant . " mark: " . $mark;
            array_merge($this->quadrants[$quadrant]->getMoveList(), $stack);
        }
    }

    public function makeMove(string $playerId, mixed $quadrant): bool
    {
        [$quadrant, $position] = $quadrant;
        if (
            !$this->isPlayer($playerId)
            || !$this->validPosition($quadrant)
            || !$this->validPosition($position)
        ) {
            return false;
        }

        $board = $this->quadrants[$quadrant];
        $playerNumber = $this->getPlayerNumber($playerId);
        if (
            !$this->isPlayersMove($playerNumber)
            || $board->isMarked()
            || $this->outOfMoves()
            || !$board->makeMove($playerId, $position, $this->moveNumber)
        ) {
            return false;
        }

        $this->updateGraph($quadrant);

        // Choose randomly which state to collapse to 
        if ($this->checkForCycle($quadrant)) {
            $this->collapseState(random_int(0, 1) ? $this->previousQuadrant : $quadrant);
            $this->handleLastQuadrant();
        }
        $this->previousQuadrant = $quadrant;
        $this->finishMove();
        return true;
    }

    private function handleLastQuadrant(): void
    {
        if ($this->getMovesLeft() !== 1) {
            return;
        }

        $playerNumber = $this->getPlayersMove() === 1 ? 2 : 1;
        $quadrant = $this->findLastQuadrant();
        $this->markQuadrant($quadrant, $playerNumber);
        $this->decrementMoves();
    }

    private function findLastQuadrant(): int
    {
        foreach ($this->quardants as $quadrant) {
            if ($quadrant->isMarked()) {
                continue;
            }
            return $quadrant->getId();
        }

        return -1;
    }

    private function checkForCycle($quadrant): bool
    {
        $map = [];
        foreach ($this->graph[$quadrant] as $node) {
            if (++$map[$node] === 2) {
                return true;
            }
        }
        return dFSCycleCheck($this->graph)($quadrant);
    }

    private function markQuadrant($quadrant, $playerNumber, $move = null): void
    {
        $this->updateBoardState($quadrant, $playerNumber);
        unset($graph[$quadrant]);
        if (isset($move)) {
            unset($edgeList[$move]);
        }
    }

    private function updateBoardState($quadrant, $playerNumber)
    {
        if (!$this->quadrantIsEmpty($quadrant, parent::getState())) {
            throw new \Exception("Unreachable state trying to add to an already marked State");
        }
        $this->updateState($quadrant, $playerNumber);
    }

    private function updateState($quadrant, $playerNumber): void
    {
        $this->quadrants[$quadrant]->mark($playerNumber);
        $this->changeState($quadrant, $playerNumber);
        $this->decrementMoves();
    }

    private function decrementMoves()
    {
        $this->setMovesLeft($this->getMovesLeft() - 1);
    }

    private function addToGraph($quadrant1, $quadrant2): void
    {
        array_push($this->edgeList, $quadrant1, $quadrant2);
        array_push($this->graph[$quadrant1], $quadrant2);
        array_push($this->graph[$quadrant2], $quadrant1);
    }
}
