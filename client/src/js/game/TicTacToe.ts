import GameBored from "./bored/GameBored";
import { Coordinates, Dimensions, QuadrantNumber } from "../types";
import { Mark } from "../common/enums";

export default class TicTacToe {
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private bored: GameBored;
  private state: number;
  private lineStroke: number;
  private winner: Mark;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ) {
    this.lineStroke = 10;
    this.context = context;
    this.setDimensions(dimensions);
    this.bored = new GameBored(context, dimensions, this.lineStroke);
    this.state = 0;
    this.winner = Mark.Empty;
  }

  public setWinner(winner: Mark): void {
    this.winner = winner;
  }
  public getWinner(): Mark {
    return this.winner;
  }

  public setDimensions(dimensions: Dimensions) {
    this.dimensions = dimensions;
  }

  public clearGame() {
    this.context.clearRect(0, 0, ...this.dimensions);
  }

  public getQuadrantNumber(coordinates: Coordinates): QuadrantNumber {
    if (this.bored.isValidMove(coordinates)) {
      console.log(this.bored.getQuadrantNumber(coordinates));
      return this.bored.getQuadrantNumber(coordinates);
    }
    return -1;
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
