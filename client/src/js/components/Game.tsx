import React, { ReactElement, useRef, useState, useEffect } from "react";
import { Link } from "react-router-dom";
import WindowController from "../game/controllers/WindowController";
import MouseController from "../game/controllers/MouseController";
import GameClient from "../game/GameClient";
import SocketServer from "../server/SocketServer";
import { Dimensions } from "../types/game";
import { Mark } from "../common/enums";
import IGame from "../game/IGame";
import Loader from "./Loader";
import { GameInfo } from "../GameData";

interface GameProps {
  setGameSelected: () => void;
  handleInvalidGame: () => void;
  gameId: string;
  updateGameId: (gameId: string) => void;
  gameInfo: GameInfo;
}

const Game = ({
  handleInvalidGame,
  gameId,
  gameInfo,
  updateGameId,
}: GameProps): ReactElement => {
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
  const [gameLoaded, setGameLoaded] = useState<boolean>(false);

  useEffect(() => {
    (async () => createGame())();
  }, []);

  useEffect(() => {
    const resetHeaderWidth = () => {
      setHeaderWidth(getDimension());
    };
    window.addEventListener("resize", resetHeaderWidth);
    return () => {
      window.removeEventListener("resize", resetHeaderWidth);
    };
  });

  const setCanvasDimensions = (
    canvas: HTMLCanvasElement,
    width: number,
    height: number
  ) => {
    canvas.height = height;
    canvas.width = width;
  };

  const getDimension = (): number => {
    const canvasContainer: HTMLDivElement = canvasContainerRef.current;
    return Math.min(canvasContainer.clientWidth, canvasContainer.clientHeight);
  };

  const loadFont = async () => {
    const font = new FontFace(
      "Press Start P",
      "url(https://fonts.gstatic.com/s/pressstart2p/v9/e3t4euO8T-267oIAQAu6jDQyK3nYivN04w.woff2)"
    );
    await font.load();
    document.fonts.add(font);
  };

  const createGame = async () => {
    //const gameId = getGameIdFromUrl(location);
    //const gameSelected = getGameSelectedFromUrl(location);
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

    const game = (gameInfo?.createGame?.(context, dimensions) as IGame) ?? "";

    // if invalid game name bail
    if (!game) {
      handleInvalidGame();
    }

    const resizeController = new WindowController(canvasContainer);
    const gameController = new MouseController();

    gameClient.current = new GameClient(
      server,
      game as IGame,
      { gameController: gameController, resizeController: resizeController },
      stateCallbacks,
      { canvas: canvas, gameId: gameId }
    );
    gameClient.current.start();
    setGameLoaded(true);
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

  const headerControls = () => {
    return (
      <div className="game-header__btn-container">
        <Link to={`/${gameInfo?.gameName ?? "arcade"}`}>
          <button onClick={resetGame} className="btn-text mdc-button">
            <span className="mdc-button__ripple"></span>
            <span className="mdc-button__label">Replay</span>
          </button>
        </Link>
        <p className="btn--seperator">/</p>
        <Link to="/arcade">
          <button className="btn-text mdc-button">
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
          {headerControls()}
        </>
      );
    } else if (gameOver) {
      return (
        <>
          <h1 className="game-header__winner">
            {winner ? `${getPlayerSymbol(winner)} Wins!` : "Tie Game"}
          </h1>
          {headerControls()}
        </>
      );
    } else if (playerDisconnected) {
      return (
        <>
          <h1 className="game-header__player-disconnect">
            {`${getOppositePlayer()} Left!`}
          </h1>
          {headerControls()}
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

  const getLoadingMessage = (): string => {
    if (!connected && !gameLoaded) {
      return "Loading";
    } else if (!connected) {
      return "Connecting";
    } else if (!gameLoaded) {
      return "Loading Game";
    }
  };

  return (
    <>
      <div className="game-container" ref={gameContainerRef}>
        <div className="game" ref={canvasContainerRef}>
          {connected && gameLoaded && (
            <header
              style={{ width: headerWidth }}
              className="game-header fade-in-long"
            >
              {getHeaderMessage()}
            </header>
          )}
          <canvas
            className="game-canvas fade-in-long"
            style={{ display: connected && gameLoaded ? "unset" : "none" }}
            ref={canvasRef}
          ></canvas>
        </div>
      </div>
      {!(connected && gameLoaded) && <Loader message={getLoadingMessage()} />}
    </>
  );
};

export default Game;
