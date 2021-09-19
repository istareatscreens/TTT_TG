import Game from "./Game";
import { Dimensions } from "./types";

const canvas = document.getElementById("ttt-canvas") as HTMLCanvasElement;
const context = canvas.getContext("2d");
window.addEventListener("resize", () => {
  init();
});

const init = () => {
  const dimensions: Dimensions = [window.innerHeight, window.innerWidth];
  setCanvasDimensions(...dimensions);
  const game = new Game(context, dimensions);
  game.start();
};

const setCanvasDimensions = (height: number, width: number) => {
  canvas.height = height;
  canvas.width = width;
};

init();
