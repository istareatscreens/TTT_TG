# TTT_TG

A full stack extensible web application for running multiplayer turn based games in canvas. Comes pre-loaded both Tic Tac Toe and Quantum Tic Tac Toe.

## Tech Stack

### Build

    - Built with docker-compose with nginx acting as a reverse proxy server

### Client

    - ReactJS framework
    - TypeScript
    - PHP 8.0 and Symfony2 for session handling
    - Webpack/Gulp for tooling
    - SCSS Preprocessor

### Server

    - PHP 8.0
    - Rachet WebSocket library
    - MySQL
    - Memcached for session storing
    - Nginx webserver

## Features

- Multiplayer between two players
- A fancy custom loading screen for slow connections
- Responsive design for mobile and desktop
- Detection when one player leaves game
- Ability to rejoin a game on browser refresh
- Server side support for multiple games played at once by a single client (not implemented in client)
- Two games, Tic Tac Toe and Quantum Tic Tac Toe
- Extensible framework for delivering turn based multiplayer games

## Running

To run simply run from the project directory:

`docker-compose up`

Navigate to localhost <b>in two different browsers (e.g. Firefox and Chrome as it only supports one client connection per browser application)</b> to test gameplay of Tic Tac Toe/Quantum Tic Tac Toe.

## Development

### Server

Testing of the server can be done by running a MySQL container with the following command from the server directory:

`docker run --name tttdb -v ${PWD}/src/Db/script/db.sql:/docker-entrypoint-initdb.d/db.sql -e MYSQL_ROOT_PASSWORD=1234 -p 3306:3306 -d mysql:latest`

After the container is launched run the following command to execute tests:

`php ./vendor/bin/phpunit --coverage-text`

### Info

Server structure is fairly simple. SocketServer.php opens the websocket connection, handles disconnects and recieves requests. MessageHandler class handles/validates requests directed from connected clients, ClientHandler class keeps track of each session and its connection. Handling of client disconnecting from multiple game is handled by the MySQL database.

### Client

From the client directory a development build of the client can be run from port localhost:3000 by executing:

`npm install`

Then running:

`npm run dev`

Production build of the client can be tested by running:

`npm run build`

### Info

The client is split into two parts. The GameClient which interacts with the ReactJS front end through callbacks and recieves requests the server and sends requests from the player. The front-end is done in ReactJS with TypeScript and has routes specified in App.js using react-router-dom. Arcade.tsx handles url parsing as well as loading the GameClient through Game.tsx and providing a game menu (GameMenu.tsx) and info screen (GameInfo.tsx) through GameSelector.tsx.

Styling is done roughly following BEM convention.

Both Tic Tac Toe and Quantum Tic Tac Toe store their board state in an integer. Where 00 is empty, 01 is X and 10 is O for example:

```
Number: 0b010101011010000000

Number      Board
01 01 01    X X X
01 10 10    X O O
00 10 00    _ O _

```

Bitwise operations are used to modify and read the state of the board.

## Extending

Addition of games to this application is relatively easy.

### Client

1. Implement IGame.ts interface for your front-end game rendering logic using canvas. Make sure your constructor takes in a context and Dimensions of the canvas
2. Add your new game and information about your game to the gamesInfo array in GameData.tsx
3. In App.js add the Route to your Game as a child element in the Switch route e.g:

```
<Route path="/QTicTacToe/:id" exact>
    <Arcade />
</Route>
<Route path="/TicTacToe/:id" exact>
    <Arcade />
</Route>
```

### Server

1. Implement GameInterface.php for your game logic. See AbstractTicTacToe.php as an example of how to setup createGame function
2. In server.php create a new instance of your game and pass it to the GameFactory instance via addGame

### Notes

- Server output is specified in GameMessage.php
- Client output is specified in Message.ts

## Security

- Project includes extensive input validation
- Nginx reverse proxy server to anonymize the backend and control traffic
- Sessions allowing control of connections to the server
- SQL templating to reduce SQL injection surface

## Preview

<figure><img src="https://i.imgur.com/HKvHlTq.png" alt="Trulli" style="width:100%"><figcaption align = "center"><b>Fig.1 - Main Menu</b></figcaption></figure>

<figure><img src="https://i.imgur.com/bjIoTNh.png" alt="Trulli" style="width:100%"><figcaption align = "center"><b>Fig.2 - Game Menu</b></figcaption></figure>

<figure><img src="https://i.imgur.com/XhHqOLM.png" alt="Trulli" style="width:100%"><figcaption align = "center"><b>Fig.3 - Game Info</b></figcaption></figure>

<figure><img src="https://i.imgur.com/k2aCBaM.png" alt="Trulli" style="width:100%"><figcaption align = "center"><b>Fig.4 - Quantum Tic Tac Toe Preview</b></figcaption></figure>

<figure><img src="https://i.imgur.com/8et3rTU.png" alt="Trulli" style="width:100%"><figcaption align = "center"><b>Fig.5 -  Tic Tac Toe Priview</b></figcaption></figure>
