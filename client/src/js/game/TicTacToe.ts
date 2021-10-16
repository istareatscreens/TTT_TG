import GameBored from "./bored/GameBoard";
import { Coordinates, Dimensions, QuadrantNumber } from "../types";
import { Mark } from "../common/enums";
import IGame from "./IGame";
import TicTacToeState from "./bored/state/TicTacToeState";

export default class TicTacToe implements IGame {
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private bored: GameBored;
  private state: TicTacToeState;
  private gameOverState: TicTacToeState;
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
    this.state = new TicTacToeState(0);
    this.turn = Mark.X;
    this.winner = Mark.Empty;
    this.gameOverState = new TicTacToeState();
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

  public isTurn(): boolean {
    return this.mark === this.turn;
  }

  public setDimensions(dimensions: Dimensions): void {
    this.dimensions = dimensions;
  }

  public clearGame(): void {
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
    if (state !== this.state.getState()) {
      this.turn = X === this.turn ? O : X;
    }
    this.state.setState(state);
    this.draw();
  }

  public setGameOverState(gameOverState: number): void {
    this.gameOverState = new TicTacToeState(gameOverState);
    this.draw();
  }

  public draw(): void {
    //87381 all X
    //174762 all O
    console.log(this.dimensions);
    this.bored = new GameBored(this.context, this.dimensions, this.lineStroke);
    this.bored.setGameOverState(this.gameOverState);
    this.bored.draw(this.state);
  }
}
