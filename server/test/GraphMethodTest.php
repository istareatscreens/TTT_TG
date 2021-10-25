<?php


namespace Test;

use Game\Game\QTicTacToe\GraphMethods;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class graphMethodTest extends TestCase
{

    protected function setUp(): void
    {

        $this->graphNoCycle = [
            0 => [1],
            1 => [2, 3, 4, 0],
            2 => [1],
            3 => [1],
            4 => [1]
        ];

        $this->graphHasCycle = [
            0 => [4, 1],
            1 => [0, 2],
            2 => [1, 3],
            3 => [2, 4],
            4 => [0, 3]
        ];

        $this->graphHasCycle2 = [
            0 => [6, 2, 8,],
            1 => [],
            2 => [0],
            3 => [],
            4 => [],
            5 => [],
            6 => [8, 0],
            7 => [],
            8 => [6, 0]
        ];

        $this->graphSingleQuadrant = [
            0 => []
        ];

        $this->graphLoop = [
            0 => [1, 1],
            1 => [0, 0]
        ];
    }


    /** @test */
    public function no_cycles()
    {
        $this->assertFalse(GraphMethods::dFSCycleCheck($this->graphNoCycle)(0));
    }


    /** @test */
    public function has_cycle()
    {
        $this->assertTrue(GraphMethods::dFSCycleCheck($this->graphHasCycle)(0));
        $this->assertTrue(GraphMethods::dFSCycleCheck($this->graphHasCycle2)(8));
    }

    /** @test */
    public function single_quadrant()
    {
        $this->assertFalse(GraphMethods::dFSCycleCheck($this->graphSingleQuadrant)(0));
    }

    /** @test */
    public function graph_has_loop()
    {
        $this->assertFalse(GraphMethods::dFSCycleCheck($this->graphLoop)(0));

        $this->assertTrue(GraphMethods::checkForParallelCycle($this->graphLoop, 0));
    }
}
