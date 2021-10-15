import { Coordinates, QuadrantNumber } from "../../../types";
import GameBoard from "../GameBoard";
import IQuadrant, { QuadrantProperties } from "./IQuadrant";
import Quadrant from "./Quadrant";

export default class QauntumQuadrant implements IQuadrant {
  private properties: QuadrantProperties;
  private markedQuadrant: Quadrant;
  private isGameBoard: boolean;
  private gameBoard: GameBoard | null;

  public constructor(
    context: CanvasRenderingContext2D,
    properties: QuadrantProperties,
    lineStroke: number,
    color: string = "black"
  ) {
    this.markedQuadrant = new Quadrant(context, properties, lineStroke, color);
    this.isGameBoard = this.checkIfQuadrantIsGameboard();
    this.gameBoard = this.isGameBoard
      ? (properties.content as GameBoard)
      : null;
  }

  public draw(): void {
    if (this.isGameBoard) {
      this.gameBoard.draw();
    } else {
      this.markedQuadrant.draw();
    }
  }

  private checkIfQuadrantIsGameboard(): boolean {
    return typeof this.properties.content === "object";
  }

  public getCenterCoordinate(): Coordinates {
    return this.markedQuadrant.getCenterCoordinate();
  }

  public isInQuadrant(xCoordinate: number, yCoordinate: number): boolean {
    return this.markedQuadrant.isInQuadrant(xCoordinate, yCoordinate);
  }

  public isEmpty(coordinates: Coordinates): boolean {
    return this.isGameBoard
      ? this.gameBoard.isValidMove(coordinates)
      : this.markedQuadrant.isEmpty();
  }

  public getNumber(): QuadrantNumber {
    return this.markedQuadrant.getNumber();
  }
}
