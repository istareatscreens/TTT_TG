import GameBored from "./GameBored";
import { Dimensions } from "./types";

export default class Game {
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private bored: GameBored;
  private state: number;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ) {
    this.dimensions = dimensions;
    this.bored = new GameBored(context, dimensions, 10);
  }

  public start() {
    this.draw();
  }

  private draw(): void {
    //87381 all X
    //174762 all O
    this.bored.draw(0);
    this.bored.draw(174762);
  }
}
