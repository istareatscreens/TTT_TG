import Grid from "./Grid";
import { Dimensions } from "./types";

export default class Bored {
  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ) {
    const grid = new Grid(context, 10, dimensions);
    grid.draw();
  }
}
