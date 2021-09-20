import Game from "./Game";
import { Dimensions } from "./types";

const canvas = document.getElementById("ttt-canvas") as HTMLCanvasElement;
const context = canvas.getContext("2d");
const dimensions: Dimensions = [window.innerWidth, window.innerHeight];
const game = new Game(context, dimensions);

const ws = new WebSocket("ws://localhost:8080");
ws.onopen = function (e) {
  console.log("Connection established!");
};

ws.onopen = () => ws.send("hey hoow are you");

window.addEventListener("resize", () => {
  init();
});

const handleClick = (e: MouseEvent) => {
  game.interact(e);
};

const init = () => {
  const dimensions: Dimensions = [window.innerWidth, window.innerHeight];
  setCanvasDimensions(...dimensions);

  canvas.removeEventListener("click", handleClick);
  canvas.addEventListener("click", handleClick);
  //use wss not ws in production
  //const ws = new WebSocket("ws://localhost:");
  game.setDimensions(dimensions);
  game.draw();
};

const setCanvasDimensions = (width: number, height: number) => {
  canvas.height = height;
  canvas.width = width;
};

init();
