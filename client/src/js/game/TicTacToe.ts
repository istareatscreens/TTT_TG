import GameBored from "./bored/GameBoard";
import {
  Coordinates,
  Dimensions,
  GameName,
  QuadrantLocation,
} from "../types/game";
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

  public getName(): GameName {
    return "TicTacToe";
  }

  public reset() {
    this.lineStroke = 10;
    //this.lineStroke = Math.min(this.dimensions[0], this.dimensions[1]) * 0.1; //lineStroke;
    this.bored = new GameBored(this.context, this.dimensions, {
      lineStroke: this.lineStroke,
      gridStroke: this.lineStroke,
      gridColor: "black",
      markColor: "black",
    });
    this.state = new TicTacToeState(0);
    this.turn = Mark.X;
    this.winner = Mark.Empty;
    this.gameOverState = new TicTacToeState();
    this.mark = Mark.Empty;
  }

  public gameIsOver(): boolean {
    return this.gameOverState.getState() !== 0;
  }

  public getTurn(): Mark {
    return this.turn;
  }

  public setTurn(turn: Mark): void {
    this.turn = turn;
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

  public getQuadrantNumber(coordinates: Coordinates): QuadrantLocation {
    if (this.bored.isValidMove(coordinates)) {
      console.log(this.bored.getQuadrantNumber(coordinates));
      return this.bored.getQuadrantNumber(coordinates);
    }
    return null;
  }

  public setState(state: number): void {
    if (state !== this.state.getState()) {
      this.state = new TicTacToeState(state);
      this.draw();
    }
  }

  public setGameOverState(gameOverState: number): void {
    this.gameOverState = new TicTacToeState(gameOverState);
    this.draw();
  }

  public draw(): void {
    //87381 all X
    //174762 all O
    console.log(this.dimensions);
    this.bored = new GameBored(this.context, this.dimensions, {
      lineStroke: this.lineStroke,
      gridStroke: this.lineStroke,
      gridColor: "black",
      markColor: "black",
    });
    this.bored.setGameOverState(this.gameOverState);
    this.bored.draw(this.state);
    //this.bored.draw(new TicTacToeState(0b101010010101101010));
    //this.bored.draw(new TicTacToeState(87381));
  }
}
