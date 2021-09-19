import Bored from "./Bored";
import { Dimensions } from "./types";

const canvas = document.getElementById("ttt-canvas") as HTMLCanvasElement;
const context = canvas.getContext("2d");
window.addEventListener("resize", () => {
  init();
});

const init = () => {
  const dimensions: Dimensions = [window.innerHeight, window.innerWidth];
  setCanvasDimensions(...dimensions);

  const bored = new Bored(context, dimensions, 10);
  bored.draw(0);
  bored.update(87381);
};

const setCanvasDimensions = (height: number, width: number) => {
  canvas.height = height;
  canvas.width = width;
};

init();
