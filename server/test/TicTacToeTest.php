<?php


namespace Test;

use Game\Db\Database;
use Game\Game\GameFactory;
use Game\Library\Uuid;
use Game\Game\TicTacToe;
use PHPUnit\Framework\TestCase;

require_once "Mock/ClientMock.php";

class TicTacToeTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new Database(true);
        $this->db->resetDb();
        $this->gameId = Uuid::v4();
        $this->playerId1 = Uuid::v4();
        $this->playerId2 = Uuid::v4();
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
        $gameFactory->addGame(new TicTacToe());
        $game = $gameFactory->createGame(
            "TicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId2
        );
        $this->assertIsObject($game);

        $this->assertEquals($game::class, TicTacToe::class);
        $this->assertEquals($game->getState(), 0);
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
        $gameFactory->addGame(new TicTacToe());
        $game = $gameFactory->createGame(
            "TicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId1
        );
        $this->assertFalse($game);
    }

    /** @test */
    public function test_game_win()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new TicTacToe());
        $game = $gameFactory->createGame(
            "TicTacToe",
            $this->gameId,
            $this->playerId1,
            $this->playerId2
        );
        $this->assertIsObject($game);

        // set player 1 to X
        if ($game->getPlayerNumber($this->playerId1) !== 1) {
            $temp = $this->playerId1;
            $this->playerId1 = $this->playerId2;
            $this->playerId2 = $temp;
        }

        $game->makeMove($this->playerId1, 0);
        $this->assertEquals(0b000000000000000001, $game->getState());
        $game->makeMove($this->playerId1, 1); // try to move when not their turn
        $this->assertEquals(0b000000000000000001, $game->getState());
        $game->makeMove($this->playerId2, 0); // try to move in quadrant already marked
        $this->assertEquals(0b000000000000000001, $game->getState());
        $game->makeMove($this->playerId2, 1);
        $this->assertEquals(0b000000000000001001, $game->getState());
        $game->makeMove($this->playerId2, 8);
        $this->assertEquals(0b000000000000001001, $game->getState());
        $game->makeMove($this->playerId1, 2);
        $this->assertEquals(0b000000000000011001, $game->getState());
        $game->makeMove($this->playerId2, 7);
        $this->assertEquals(0b001000000000011001, $game->getState());
        $game->makeMove($this->playerId2, 9);
        $this->assertEquals(0b001000000000011001, $game->getState());
        $game->makeMove($this->playerId2, -1);
        $this->assertEquals(0b001000000000011001, $game->getState());
        $game->makeMove($this->playerId1, 5);
        $this->assertEquals(0b001000010000011001, $game->getState());
        $game->makeMove($this->playerId2, 4);
        $this->assertEquals(0b001000011000011001, $game->getState());
        $this->assertTrue($game->gameOver()); //game should be over
        $this->assertEquals($game->getWinner(), 2);
        $this->assertEquals($game->getWinningState(), 0b1000001000001000);
        $game->makeMove($this->playerId1, 3); //try to change state after gameover
        $this->assertEquals(0b001000011000011001, $game->getState());
    }

    public function test_game_tie()
    {
        //create game
        $gameFactory = new GameFactory();
        $gameFactory->addGame(new TicTacToe());
        $game = $gameFactory->createGame(
            "TicTacToe",
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

        $game->makeMove($this->playerId1, 0);
        $game->makeMove($this->playerId2, 1);
        $game->makeMove($this->playerId1, 2);
        $game->makeMove($this->playerId2, 5);
        $game->makeMove($this->playerId1, 3);
        $game->makeMove($this->playerId2, 8);
        $game->makeMove($this->playerId1, 4);
        $game->makeMove($this->playerId2, 6);
        $game->makeMove($this->playerId1, 7);
        $this->assertEquals(0b100110100101011001, $game->getState());
        $this->assertTrue($game->gameOver()); //game should be over
        $this->assertEquals($game->getWinner(), 0);
        $game->makeMove($this->playerId1, 5);
        $game->makeMove($this->playerId2, 8);
        $game->makeMove($this->playerId1, 1);
        $game->makeMove($this->playerId2, 7);
        $this->assertEquals(0b100110100101011001, $game->getState());
    }
}
