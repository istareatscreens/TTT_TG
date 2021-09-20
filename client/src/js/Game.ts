import GameBored from "./GameBored";
import { Dimensions } from "./types";

export default class Game {
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private bored: GameBored;
  private state: number;
  private lineStroke: number;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ) {
    this.lineStroke = 10;
    this.context = context;
    this.setDimensions(dimensions);
    this.bored = new GameBored(context, dimensions, this.lineStroke);
    this.state = 0;
  }

  public setDimensions(dimensions: Dimensions) {
    this.dimensions = dimensions;
  }

  public clearGame() {
    this.context.clearRect(0, 0, ...this.dimensions);
  }

  public interact(event: MouseEvent): void {
    const { offsetX, offsetY } = event;
    console.log(this.bored.getQuadrantNumber([offsetX, offsetY]));
  }

  public setState(state: number): void {
    this.state = state;
  }

  public draw(): void {
    //87381 all X
    //174762 all O
    this.bored = new GameBored(this.context, this.dimensions, this.lineStroke);
    this.bored.draw(this.state);
  }
}
