import React, { ReactElement, useRef, useState, useEffect } from "react";
import WindowController from "../game/controllers/WindowController";
import MouseController from "../game/controllers/MouseController";
import GameClient from "../game/GameClient";
import TicTacToe from "../game/TicTacToe";
import SocketServer from "../server/SocketServer";
import { Dimensions } from "../types";
import { Mark } from "../common/enums";
import IServer from "../server/IServer";

interface Props {}

export default function Game(): ReactElement {
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
  const [player2Connected, setPlayer2Connected] = useState<boolean>(true);

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

  //setup game
  useEffect(() => {
    const stateCallbacks = {
      setPlayerNumber,
      setTurn,
      setWinner,
      setGameStatus,
      setGameOver,
    };

    const canvasContainer: HTMLDivElement = canvasContainerRef.current;
    const canvas: HTMLCanvasElement = canvasRef.current;

    const context = canvas.getContext("2d");

    const dimension = getDimension();
    const dimensions: Dimensions = [dimension, dimension];

    setCanvasDimensions(canvas, ...dimensions);
    setHeaderWidth(dimension);

    // create game
    const server = new SocketServer();
    const game = new TicTacToe(context, dimensions);
    const resizeController = new WindowController(canvasContainer);
    const gameController = new MouseController();

    gameClient.current = new GameClient(
      server,
      game,
      { gameController: gameController, resizeController: resizeController },
      canvas,
      stateCallbacks
    );
    gameClient.current.start();
  }, []);

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

  return (
    <div className="game-container" ref={gameContainerRef}>
      <div className="game" ref={canvasContainerRef}>
        <header style={{ width: headerWidth }} className="game-header">
          <h1 className="game-header__player">
            You: {getPlayerSymbol(playerNumber)}
          </h1>
          {gameOver ? (
            <h1 className="game-header__winner">
              {winner ? `${getPlayerSymbol(winner)} Wins!` : "Tie Game"}
            </h1>
          ) : (
            <h1 className="game-header__turn">
              {`${getPlayerSymbol(turn)}'s Turn`}
            </h1>
          )}
        </header>
        <canvas className="game-canvas" ref={canvasRef}></canvas>
      </div>
    </div>
  );
}
