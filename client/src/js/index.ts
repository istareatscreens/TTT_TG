import MouseController from "./game/controllers/MouseController";
import WindowController from "./game/controllers/WindowController";
import GameClient from "./game/GameClient";
import TicTacToe from "./game/TicTacToe";
import SocketServer from "./server/SocketServer";
import { Dimensions } from "./types";

const setCanvasDimensions = (width: number, height: number) => {
  canvas.height = height;
  canvas.width = width;
};

const canvas = document.getElementById("ttt-canvas") as HTMLCanvasElement;
const context = canvas.getContext("2d");
const dimensions: Dimensions = [window.innerWidth, window.innerHeight];
setCanvasDimensions(...dimensions);

const init = () => {
  const dimensions: Dimensions = [window.innerWidth, window.innerHeight];
  const server = new SocketServer();
  const game = new TicTacToe(context, dimensions);
  //game.draw();
  const resizeController = new WindowController(window);
  const mouseController = new MouseController();
  const client = new GameClient(
    server,
    game,
    mouseController,
    resizeController,
    canvas
  );
  client.start();
};

init();
