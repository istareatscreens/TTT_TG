<?php
namespace GameClient;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require_once "Game.php";

class Client implements MessageComponentInterface {
    protected $clients;
    protected $games;
    protected $lobby;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->lobby = new \SplQueue;
        $this->games = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    /*
        type: joinLobby | joinGame | makeMove
        gameId: default == -1 | id
        quadrant: -1<value<9
    */

    /*
        status: inLobby | inGame | failed
        state: 
        gameId: 
        winner: 0 | 1 | 2
    */

    public function onMessage(ConnectionInterface $from, $msg): void {
        //template code
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        $msg = json_decode($msg);
        $this->handleMessage($msg, $from);
        
        /*
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);

            }
        }
        */
    }

    private function handleMessage($msg, $client): void{
        switch($msg->type){
            case "joinLobby":
                $this->addToLobby($client);
                $this->matchPlayer($client); 
                break;
            case "makeMove":
                $this->handleMove($msg, $client);
                break;
            case "joinGame":
                break;
        }
    }

    /*
        status: inLobby | inGame | gameOver
        state: 
        gameId: 
        winner: 0 | 1 | 2
    */
    private function handleMove($msg, $client){
        $msg = json_decode($msg);
        $id = $msg->gameId;
        $game = $this->games[$id];
        $quadrant = $msg->quadrant;
        $winner = 0;
        if($game->makeMove($client, $quadrant)){
            $winner = $game->getWinner();
            $data = array();
            $data["status"] = ($winner === 0)? "inGame": "gameOver";
            $data["state"] = $game->getState();
            $data["gameId"] = $msg->gameId;
            $data["winner"] = $winner;
            $data = json_encode($data);
            $players = $game->getPlayers();
            $players[0]->send($data);
            $players[1]->send($data);
        }

        if($winner !== 0){
            unset($this->games[$id]);
        }
    }
    
    private function addToLobby($client): void{
        $this->lobby->push($client);
    }

    private function matchPlayer($client): void{
        echo "\n" ;
        echo count($this->lobby);
        echo "\n"; 
        if(count($this->lobby) !== 2){

            echo "\n"; 
            echo "HERE in lobby code:";
            echo "\n"; 
            $data = array();
            $data["status"] = "inLobby";
            $data["gameId"] = -1;
            $data["winner"] = 0;
            $data = json_encode($data);
            $client->send($data);
            return;
        }
            echo "\n"; 
        echo "IN GAME RUN";
            echo "\n"; 
        $client1 = $this->lobby->pop();
        $client2 = $this->lobby->pop();
        $this->createGame($client1, $client2);
    }

    private function createGame($client1, $client2): void{
        if($client1 === $client2){
           return; 
        }
        $gameId = random_int(0, PHP_INT_MAX);
        $this->games[$gameId] = new Game($gameId, $client1, $client2);
        $data = array();
        $data["status"] = "inGame";
        $data["gameId"] = $gameId;
        $data["winner"] = 0;
        $data = json_encode($data);
        $client1->send($data);
        $client2->send($data);

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}