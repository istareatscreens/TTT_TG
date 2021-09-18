import Bored from "./Bored";

interface Dimensions {
  width: number;
  height: number;
}

const canvas = document.getElementById("ttt-canvas") as HTMLCanvasElement;
const context = canvas.getContext("2d");
window.addEventListener("resize", () => {
  init();
});

const init = () => {
  const dimensions = {
    width: window.innerHeight,
    height: window.innerWidth,
  };

  canvas.height = dimensions.height;
  canvas.width = dimensions.width;
  const bored = new Bored(context, dimensions);
};

init();
