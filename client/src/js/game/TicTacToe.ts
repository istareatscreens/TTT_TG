import GameBored from "./bored/GameBored";
import { Coordinates, Dimensions, QuadrantNumber } from "../types";
import { Mark } from "../common/enums";

export default class TicTacToe {
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private bored: GameBored;
  private state: number;
  private gameOverState: number;
  private lineStroke: number;
  private winner: Mark;
  private turn: Mark;
  private mark: Mark;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions
  ) {
    this.context = context;
    this.setDimensions(dimensions);
    this.reset();
  }

  public reset() {
    this.lineStroke = 10;
    this.bored = new GameBored(this.context, this.dimensions, this.lineStroke);
    this.state = 0;
    this.turn = Mark.X;
    this.winner = Mark.Empty;
    this.gameOverState = 0;
    this.mark = Mark.Empty;
  }

  public getTurn(): Mark {
    return this.turn;
  }

  public setWinner(winner: Mark): void {
    this.winner = winner;
  }
  public getWinner(): Mark {
    return this.winner;
  }

  public setMark(mark: Mark): void {
    this.mark = mark;
  }

  public getMark(): Mark {
    return this.mark;
  }

  public isTurn() {
    return this.mark === this.turn;
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
    return null;
  }

  public setState(state: number): void {
    const { X, O } = Mark;
    if (state !== this.state) {
      this.turn = X === this.turn ? O : X;
    }
    this.state = state;
    this.draw();
  }

  public setGameOverState(gameOverState: number): void {
    this.gameOverState = gameOverState;
    this.draw();
  }

  public draw(): void {
    //87381 all X
    //174762 all O
    this.bored = new GameBored(this.context, this.dimensions, this.lineStroke);
    this.bored.setGameOverState(this.gameOverState);
    this.bored.draw(this.state);
  }
}
