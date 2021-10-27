<?php

namespace Game\Game\QTicTacToe;

use Game\Game\AbstractTicTacToe;
use Game\Game\GameInterface;
use Game\Game\PlayerAssignment\RandomAssign;
use Game\Game\PlayerAssignment\SetAssign;
use Game\Game\TicTacToe;
use Game\Game\QTicTacToe\QQuadrant;
use function Game\Game\QTicTacToe\dFSCycleCheck;

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
                $ttt->createGame($i, ...$players),
                $i
            );
        }
        return $newGame;
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
        $this->setPlayersMove(($this->getPlayersMove() === 1) ? 2 : 1);
        $this->changeTurnInQuadrant($this->getPlayersMove());
    }

    private function changeTurnInQuadrant(int $playersMove): void
    {
        foreach ($this->quadrants as $quadrant) {
            $quadrant->setPlayersMove($playersMove);
        }
    }

    private function updateGraph($quadrant): void
    {
        // not last move for player
        if ($this->moves !== 1) {
            return;
        }
        $this->addToGraph($quadrant, $this->previousQuadrant);
    }

    public function validPosition($quadrant): bool
    {
        try {
            return is_array($quadrant) &&
                count($quadrant) === 2
                && parent::validPosition($quadrant[0])
                && parent::validPosition($quadrant[1]);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function collapseState($quadrant): void
    {

        $getConnectedQuadrant = function ($move) {
            [$quadrant1, $quadrant2] = $this->edgeList[$move];
            return ($this->quadrants[$quadrant1]->isMarked()) ? $quadrant2 : $quadrant1;
        };

        $visited = array_fill(0, $this->moveNumber + 1, false);

        // handle initial quadrant
        $moveList = $this->quadrants[$quadrant]->getMoveList();
        [$move, $playerNumber] = array_pop($moveList);
        //$this->quadrants[$quadrant]->mark($mark);
        //$this->markQuadrant($quadrant, $mark, $move);
        $this->markQuadrant($quadrant, $playerNumber);
        $playerNumbers[$quadrant] = $playerNumber;
        $visited[$move] = true;

        while (count($moveList)) {

            [$move, $playerNumber] = array_pop($moveList);
            $quadrant = $getConnectedQuadrant($move);
            if ($visited[$move] || $this->quadrants[$quadrant]->isMarked()) {
                echo "exit \n";
                continue;
            }
            $visited[$move] = true;
            $playerNumbers[$quadrant] = $playerNumber;
            // $this->markQuadrant($quadrant, $mark, $move);
            //$this->quadrants[$quadrant]->mark($mark);
            $this->markQuadrant($quadrant, $playerNumber);
            $moveList = [...$this->quadrants[$quadrant]->getMoveList(), ...$moveList];
        }
    }

    private function markQuadrant(int $quadrant, int $playerNumber)
    {
        if (!$this->quadrantIsEmpty($quadrant, parent::getState())) {
            echo "Error: QTicTacToe impossible to reach point markQuadrant";
            return;
        }
        $this->quadrants[$quadrant]->mark($playerNumber);
        $this->decrementMoves();
        $this->changeState($quadrant, $playerNumber);
    }

    public function makeMove(string $playerId, mixed $quadrant): bool
    {
        [$quadrant, $position] = $quadrant;
        if (
            !$this->isPlayer($playerId)
            || !parent::validPosition($quadrant)
            || !parent::validPosition($position)
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
            $this->wonGame();
        }
        $this->previousQuadrant = $quadrant;
        $this->finishMove();
        return true;
    }

    private function wonGame(): void
    {
        $player1WinningStates = $this->findWinningStates(1);
        $player2WinningStates = $this->findWinningStates(2);
        $states = [...$player1WinningStates, ...$player2WinningStates];
        echo "\nPlayer1 States";
        print_r($player1WinningStates);
        echo "\nPlayer 2 States";
        print_r($player2WinningStates);
        echo "\nP1 Count";
        echo count($player1WinningStates);
        echo "\nP2 Count";
        echo count($player2WinningStates);
        echo "\n";
        if (!count($player1WinningStates) && !count($player2WinningStates)) {
            return;
        } else if (count($player1WinningStates) > count($player2WinningStates)) {
            $this->setWinner(1);
            $this->setWinningState($states);
        } else if (count($player1WinningStates) < count($player2WinningStates)) {
            $this->setWinner(2);
            $this->setWinningState($states);
        } else if (count($player1WinningStates) === count($player2WinningStates)) {
            $this->setWinner(0);
            $this->setWinningState($states);
        } else {
            throw new \Exception("Unreachable condition in wonGame");
        }
    }

    protected function setWinningState($winningStates)
    {
        $finalState = 0;
        foreach ($winningStates as $state) {
            $finalState |= $state;
        }
        parent::setWinningState($finalState);
    }

    private function assignMarkToMask($playerNumber, $mask)
    {
        return ($playerNumber === 1) ?
            $mask & 0b010101010101010101 :
            $mask & 0b101010101010101010;
    }

    private function findWinningStates($playerNumber): array
    {
        $states = [];
        foreach (parent::$winningMasks as $mask) {
            $result = parent::getState() & $mask;
            if ($this->containsThreeOfTheSameMarks($result, $playerNumber)) {
                array_push($states, $this->assignMarkToMask($playerNumber, $mask));
            }
        }
        return $states;
    }

    private function handleLastQuadrant(): void
    {
        if ($this->getMovesLeft() !== 1) {
            return;
        }

        $playerNumber = $this->getPlayersMove() === 1 ? 2 : 1;
        $quadrant = $this->findLastQuadrant();
        //$this->markQuadrant($quadrant, $playerNumber);
        $this->quadrants[$quadrant]->mark($playerNumber);
        $this->decrementMoves();
    }

    private function findLastQuadrant(): int
    {
        foreach ($this->quadrants as $quadrant) {
            if ($quadrant->isMarked()) {
                continue;
            }
            return $quadrant->getId();
        }

        return -1;
    }

    private function checkForCycle($quadrant): bool
    {
        return GraphMethods::checkForParallelCycle($this->graph, $quadrant) ||
            GraphMethods::dFSCycleCheck($this->graph)($quadrant);
    }

    public function getState(): mixed
    {
        if ($this->outOfMoves()) {
            return parent::getState();
        }
        $state = [];
        foreach ($this->quadrants as $quadrant) {
            array_push(
                $state,
                $quadrant->getState(
                    $this->moveNumber,
                    $this->moves === 1
                )
            );
        }
        return $state;
    }

    private function decrementMoves()
    {
        $this->setMovesLeft($this->getMovesLeft() - 1);
    }

    private function addToGraph($quadrant1, $quadrant2): void
    {
        array_push($this->edgeList, [$quadrant1, $quadrant2]);
        array_push($this->graph[$quadrant1], $quadrant2);
        array_push($this->graph[$quadrant2], $quadrant1);
    }
}
