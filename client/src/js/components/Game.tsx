import React, { ReactElement, useRef, useState, useEffect } from "react";
import { useLocation, useHistory, Link } from "react-router-dom";
import { validate as validateUuid } from "uuid";
import WindowController from "../game/controllers/WindowController";
import MouseController from "../game/controllers/MouseController";
import GameClient from "../game/GameClient";
import TicTacToe from "../game/TicTacToe";
import SocketServer from "../server/SocketServer";
import { Dimensions, GameName } from "../types/game";
import { Mark } from "../common/enums";
import Loader from "./Loader";
import { Location } from "history";
import { UniqueId } from "../common/UniqueId";
import QTicTacToe from "../game/QTicTacToe";
import IGame from "../game/IGame";

interface GameProps {
  gameSelected: GameName | "";
  setGameSelected: () => void;
}

const Game = ({ gameSelected, setGameSelected }: GameProps): ReactElement => {
  const gameContainerRef = useRef(null);
  const canvasRef = useRef(null);
  const canvasContainerRef = useRef(null);
  const gameClient = useRef<GameClient>(null);

  const [playerNumber, setPlayerNumber] = useState<Mark>(null);
  const [turn, setTurn] = useState<Mark>(null);
  const [winner, setWinner] = useState<Mark>(null);
  const [gameStatus, setGameStatus] = useState<string>(null);
  const [headerWidth, setHeaderWidth] = useState<number>(0);
  const [gameOver, setGameOver] = useState<boolean>(false);
  const [playerDisconnected, setPlayerDisconnected] = useState<boolean>(false);
  const [inLobby, setInLobby] = useState<boolean>(true);
  const [connected, setConnected] = useState<boolean>(false);
  const [gameId, setGameId] = useState<string>("");
  const history = useHistory();

  const location = useLocation();

  const setCanvasDimensions = (
    canvas: HTMLCanvasElement,
    width: number,
    height: number
  ) => {
    canvas.height = height;
    canvas.width = width;
  };

  useEffect(() => {
    const resetHeaderWidth = () => {
      setHeaderWidth(getDimension());
    };
    window.addEventListener("resize", resetHeaderWidth);
    return () => {
      window.removeEventListener("resize", resetHeaderWidth);
    };
  });

  const getDimension = (): number => {
    const canvasContainer: HTMLDivElement = canvasContainerRef.current;
    return Math.min(canvasContainer.clientWidth, canvasContainer.clientHeight);
  };

  const getGameIdFromUrl = (location: Location<unknown>): string => {
    const gameId: string = location.pathname.substring(1);
    console.log(gameId);
    console.log(validateUuid(gameId));
    return validateUuid(gameId) ? gameId : "";
  };

  const handleInvalidGame = () => {
    history.push("/");
    console.log("Invalid route", "/");
  };
  const updateGameId = (gameId: string) => {
    // url hack
    window.history.replaceState(null, "Tic Tac Toe", `/#/${gameId}`);
    history.push(`/${gameId}`);
  };

  const getSelectedGame = (
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ): IGame | void => {
    switch (gameSelected) {
      case "QTicTacToe":
        return new QTicTacToe(context, dimensions);
      case "TicTacToe":
        return new TicTacToe(context, dimensions);
      default:
        handleInvalidGame();
        setGameSelected();
    }
  };

  const loadFont = async () => {
    const font = new FontFace(
      "Press Start P",
      "url(https://fonts.gstatic.com/s/pressstart2p/v9/e3t4euO8T-267oIAQAu6jDQyK3nYivN04w.woff2)"
    );
    await font.load();
    document.fonts.add(font);
  };

  useEffect(() => {
    (async () => createGame())();
  }, []);

  const createGame = async () => {
    const gameId = getGameIdFromUrl(location);
    setGameId(gameId);

    await loadFont();
    await document.fonts.ready;

    // gameId = gameId ? "" : gameId;
    const stateCallbacks = {
      setPlayerNumber,
      setTurn,
      setWinner,
      setGameStatus,
      setGameOver,
      setPlayerDisconnected,
      setInLobby,
      setConnected,
      handleInvalidGame,
      updateGameId,
    };

    const canvasContainer: HTMLDivElement = canvasContainerRef.current;
    const canvas: HTMLCanvasElement = canvasRef.current;

    const context = canvas.getContext("2d");
    context.save();

    const dimension = getDimension();
    const dimensions: Dimensions = [dimension, dimension];

    setCanvasDimensions(canvas, ...dimensions);
    setHeaderWidth(dimension);

    // create game
    const server = new SocketServer();

    const game = getSelectedGame(context, dimensions) as IGame;
    const resizeController = new WindowController(canvasContainer);
    const gameController = new MouseController();

    gameClient.current = new GameClient(
      server,
      game,
      { gameController: gameController, resizeController: resizeController },
      stateCallbacks,
      { canvas: canvas, gameId: gameId }
    );
    gameClient.current.start();
  };

  const getPlayerSymbol = (number: Mark): string => {
    switch (number) {
      case Mark.X:
        return "X";
      case Mark.O:
        return "O";
      default:
        return "-";
    }
  };

  const resetGame = () => {
    setGameOver(false);
    setWinner(null);
    setPlayerDisconnected(false);
    setPlayerNumber(null);
    setInLobby(false);
    setGameStatus(null);
    setTurn(null);
    gameClient.current.reset();
  };

  const replayButton = () => {
    return (
      <div className="game-header__btn-container">
        <button onClick={resetGame} className="btn-text mdc-button">
          <span className="mdc-button__ripple"></span>
          <span className="mdc-button__label">Replay</span>
        </button>
        <Link to="/arcade">
          <button
            onClick={() => setGameSelected()}
            className="btn-text mdc-button"
          >
            <span className="mdc-button__ripple"></span>
            <span className="mdc-button__label">Back</span>
          </button>
        </Link>
      </div>
    );
  };

  const getOppositePlayer = (): string => {
    const { X, O } = Mark;
    return getPlayerSymbol(playerNumber == X ? O : X);
  };

  const getHeaderMessage = (): ReactElement => {
    if (gameOver && winner === playerNumber) {
      return (
        <>
          <h1 className="game-header__you-win">You Win!</h1>
          {replayButton()}
        </>
      );
    } else if (gameOver) {
      return (
        <>
          <h1 className="game-header__winner">
            {winner ? `${getPlayerSymbol(winner)} Wins!` : "Tie Game"}
          </h1>
          {replayButton()}
        </>
      );
    } else if (playerDisconnected) {
      return (
        <>
          <h1 className="game-header__player-disconnect">
            {`${getOppositePlayer()} Left!`}
          </h1>
          {replayButton()}
        </>
      );
    } else if (inLobby) {
      return (
        <>
          <h1 className="game-header__player-in-lobby">
            Waiting for Player...
          </h1>
        </>
      );
    } else {
      return (
        <>
          <h1 className="game-header__player">
            Player {getPlayerSymbol(playerNumber)}
          </h1>
          <h1 className="game-header__turn">
            {`${getPlayerSymbol(turn)}'s Turn`}
          </h1>
        </>
      );
    }
  };

  return (
    <>
      <div className="game-container" ref={gameContainerRef}>
        <div className="game" ref={canvasContainerRef}>
          {connected && (
            <header
              style={{ width: headerWidth }}
              className="game-header fade-in-long"
            >
              {getHeaderMessage()}
            </header>
          )}
          <canvas
            className="game-canvas"
            style={
              {} //{ display: connected ? "unset" : "none" }
            }
            ref={canvasRef}
          ></canvas>
        </div>
      </div>
      {
        //!connected && <Loader message="Connecting" />
      }
    </>
  );
};

export default Game;
