import { Coordinates, QuadrantNumber } from "../../../types";
import GameBoard from "../GameBoard";
import TicTacToeState from "../state/TicTacToeState";
import IQuadrant, { QuadrantProperties } from "./IQuadrant";
import Quadrant from "./Quadrant";

export default class QauntumQuadrant implements IQuadrant {
  private properties: QuadrantProperties;
  private markedQuadrant: Quadrant;
  private gameBoard: GameBoard | null;

  public constructor(
    context: CanvasRenderingContext2D,
    properties: QuadrantProperties,
    lineStroke: number,
    color: string = "black"
  ) {
    this.markedQuadrant = new Quadrant(context, lineStroke, properties, color);
    this.gameBoard = new GameBoard(context, properties.dimensions, lineStroke);
  }

  public draw(): void {
    this.gameBoard.draw(new TicTacToeState(this.properties.content as number));
  }

  public getCenterCoordinate(): Coordinates {
    return this.markedQuadrant.getCenterCoordinate();
  }

  public isInQuadrant(xCoordinate: number, yCoordinate: number): boolean {
    return this.markedQuadrant.isInQuadrant(xCoordinate, yCoordinate);
  }

  public isEmpty(coordinates: Coordinates): boolean {
    return this.gameBoard.isValidMove(coordinates);
  }

  public getNumber(): QuadrantNumber {
    return this.markedQuadrant.getNumber();
  }
}
