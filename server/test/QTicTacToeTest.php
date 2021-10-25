<?php


namespace Test;

use Game\Db\Database;
use Game\Game\GameFactory;
use Game\Library\Uuid;
use Game\Game\QTicTacToe\QTicTacToe;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class QTicTacToeTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database(true);
        $this->db->resetDb();
        $this->gameId = Uuid::v4();
        $this->playerId1 = Uuid::v4();
        $this->playerId2 = Uuid::v4();
        $this->initialState = [
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
            "0,-1,-1,-1,-1,-1,-1,-1,-1,-1"
        ];
    }

    protected function tearDown(): void
    {
        $this->db->resetDb();
    }

    /** @test */
    public function game_factory_creation_and_getters()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new QTicTacToe());
        $game = $gameFactory->createGame(
            "QTicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId2
        );
        $this->assertIsObject($game);

        $this->assertEquals($game::class, QTicTacToe::class);
        $this->assertEquals($this->initialState, $game->getState());
        $number1 = $game->getPlayerNumber($this->playerId1);
        $number2 = $game->getPlayerNumber($this->playerId2);

        $this->assertTrue($number1 === 1 || $number1 === 2);
        $this->assertTrue($number2 === 1 || $number2 === 2);
        $this->assertFalse($number1 === $number2);
        $this->assertFalse($game->gameOver());
        $this->assertEquals($game->getWinner(), 0);
    }

    /** @test */
    public function validateClient_same_playerId()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new QTicTacToe());
        $game = $gameFactory->createGame(
            "QTicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId1
        );
        $this->assertFalse($game);
    }

    public function test_game_win()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new QTicTacToe());
        $game = $gameFactory->createGame(
            "QTicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId2
        );
        $this->assertIsObject($game);
        $this->assertFalse($game->gameOver());

        // set player 1 to X
        if ($game->getPlayerNumber($this->playerId1) !== 1) {
            $temp = $this->playerId1;
            $this->playerId1 = $this->playerId2;
            $this->playerId2 = $temp;
        }

        $this->assertTrue($game->makeMove($this->playerId1, [8, 8]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "L" . bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId1, [6, 8]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertFalse($game->makeMove($this->playerId1, [4, 3]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId2, [6, 7]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "L" . bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertFalse($game->makeMove($this->playerId1, [6, 7]));
        $this->assertFalse($game->makeMove($this->playerId1, [0, 8]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "L" . bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertFalse($game->gameOver());
        $this->assertTrue($game->makeMove($this->playerId2, [0, 8]));
        $this->assertEquals(
            [
                bindec("100000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertFalse($game->makeMove($this->playerId2, [0, 7]));
        $this->assertEquals(
            [
                bindec("100000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId1, [0, 7]));
        $this->assertEquals(
            [
                "L" . bindec("100100000000000000") . ",-1,-1,-1,-1,-1,-1,-1,2,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId1, [2, 8]));
        $this->assertEquals(
            [
                bindec("100100000000000000") . ",-1,-1,-1,-1,-1,-1,-1,2,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId2, [0, 5]));
        $this->assertEquals(
            [
                "L" . bindec("100100100000000000") . ",-1,-1,-1,-1,-1,3,-1,2,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertFalse($game->makeMove($this->playerId2, [0, 4]));
        $this->assertEquals(
            [
                "L" . bindec("100100100000000000") . ",-1,-1,-1,-1,-1,3,-1,2,1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        // Cycle occurs here
        $this->assertTrue($game->makeMove($this->playerId2, [8, 7]));
        $state = $game->getState();
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertFalse($game->makeMove($this->playerId2, [4, 0]));
        $state = $game->getState();

        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertTrue($game->makeMove($this->playerId1, [5, 0]));
        $state = $game->getState();
        $quadrant = "L" . bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                $quadrant,
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    $quadrant,
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertTrue($game->makeMove($this->playerId1, [7, 0]));
        $state = $game->getState();
        $quadrant = bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                $quadrant,
                "2",
                $quadrant,
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    $quadrant,
                    "1",
                    $quadrant,
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertTrue($game->makeMove($this->playerId2, [7, 1]));
        $state = $game->getState();
        $quadrant = "L" . bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1",
                "2",
                $quadrant,
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    $quadrant,
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertTrue($game->makeMove($this->playerId2, [3, 0]));
        $state = $game->getState();
        $quadrant = bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                $quadrant,
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1",
                "2",
                bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    $quadrant,
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    bindec("000000000000000001") . ",4,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        // test invalid move after collapse
        $this->assertTrue($game->makeMove($this->playerId1, [5, 2]));
        $state = $game->getState();
        $quadrant = "L" . bindec("000000000000010001") . ",4,-1,6,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                $quadrant,
                "2",
                bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    $quadrant,
                    "1",
                    bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        $this->assertTrue($game->makeMove($this->playerId1, [4, 0]));
        $state = $game->getState();
        $quadrant =  bindec("000000000000000001") . ",6,-1,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                $quadrant,
                bindec("000000000000010001") . ",4,-1,6,-1,-1,-1,-1,-1,-1",
                "2",
                bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                    $quadrant,
                    bindec("000000000000010001") . ",4,-1,6,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        $this->assertTrue($game->makeMove($this->playerId2, [4, 1]));
        $state = $game->getState();
        $quadrant =  "L" . bindec("000000000000001001") . ",6,7,-1,-1,-1,-1,-1,-1,-1";
        $this->assertTrue(
            [
                "2",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "1",
                bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                $quadrant,
                bindec("000000000000010001") . ",4,-1,6,-1,-1,-1,-1,-1,-1",
                "2",
                bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                "1"
            ] == $state
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000000010") . ",5,-1,-1,-1,-1,-1,-1,-1,-1",
                    $quadrant,
                    bindec("000000000000010001") . ",4,-1,6,-1,-1,-1,-1,-1,-1",
                    "1",
                    bindec("000000000000001001") . ",4,5,-1,-1,-1,-1,-1,-1,-1",
                    "2"
                ] == $state
        );

        // collapse state
        $this->assertTrue($game->makeMove($this->playerId2, [3, 1]));
        $state = $game->getState();
        $quadrant = bindec("000000000000001010") . ",5,7,-1,-1,-1,-1,-1,-1,-1";
        print_r($state);
        $this->assertTrue(
            0b100101011010010010 == $state ||
                0b10110011010010010 == $state ||
                0b101001010110010010 == $state ||
                0b11010010110010010 == $state
        );

        /*
        O win
        10 01 01 
        01 10 10
        01 00 10
            
        Tie
        01 01 10
        01 10 10
        01 00 10
            
        X win
        10 10 01 
        01 01 10
        01 00 10
            
        Tie 
        01 10 10 
        01 01 10
        01 00 10
        */

        if ($state == 0b100101011010010010) {
            $this->assertEquals($game->getWinner(), 2);
            $this->assertEquals(0b100000001000000010, $game->getWinningState());
        } else if (0b10110011010010010 == $state) {
            $this->assertEquals(0, $game->getWinner());
            $this->assertEquals(0b10010010010010010, $game->getWinningState());
        } else if (0b101001010110010010 == $state) {
            $this->assertEquals(1, $game->getWinner());
            $this->assertEquals(0b000001000100010000, $game->getWinningState());
        } else if (0b11010010110010010 == $state) {
            $this->assertEquals(0, $game->getWinner());
            $this->assertEquals(0b010010010010010010, $game->getWinningState());
        } else {
            $this->assertTrue(false);
        }

        $this->assertTrue($game->gameOver());
    }

    public function test_parallel_loop_in_game()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new QTicTacToe());
        $game = $gameFactory->createGame(
            "QTicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId2
        );
        $this->assertIsObject($game);

        //set player 1 to X
        if ($game->getPlayerNumber($this->playerId1) !== 1) {
            $temp = $this->playerId1;
            $this->playerId1 = $this->playerId2;
            $this->playerId2 = $temp;
        }

        $this->assertTrue($game->makeMove($this->playerId1, [8, 8]));
        $this->assertEquals(
            [
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "L" . bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId1, [0, 8]));
        $this->assertEquals(
            [
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId2, [0, 7]));
        $this->assertEquals(
            [
                "L" . bindec("011000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,1,0",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                bindec("010000000000000000") . ",-1,-1,-1,-1,-1,-1,-1,-1,0",
            ],
            $game->getState()
        );

        $this->assertTrue($game->makeMove($this->playerId2, [8, 7]));
        print_r($game->getState());
        $this->assertTrue(
            [
                "1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                "2"
            ] == $game->getState()
                ||
                [
                    "2",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
                    "1"
                ] == $game->getState()

        );
    }
}
