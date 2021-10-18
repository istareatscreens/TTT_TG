import { Coordinates, Dimensions, QuadrantNumber } from "../../../types";
import GameBoard from "../GameBoard";
import TicTacToeState from "../state/TicTacToeState";
import IQuadrant, { QuadrantProperties } from "./IQuadrant";
import Quadrant from "./Quadrant";

export default class QauntumQuadrant implements IQuadrant {
  private properties: QuadrantProperties;
  private markedQuadrant: Quadrant;
  private gameBoard: GameBoard | null;
  private edgeBuffer: number;

  public constructor(
    context: CanvasRenderingContext2D,
    properties: QuadrantProperties,
    lineStroke: number,
    color: string = "black"
  ) {
    this.edgeBuffer = this.calculateEdgeBuffer(...properties.dimensions);
    this.markedQuadrant = new Quadrant(context, lineStroke, properties, color);
    this.gameBoard = new GameBoard(
      context,
      this.adjustDimensions(...properties.dimensions, ...properties.gridOffset),
      {
        lineStroke: lineStroke,
        gridStroke: lineStroke,
        gridColor: color,
        markColor: color,
      },
      this.adjustCoordinates(
        ...properties.coordinates,
        ...properties.gridOffset
      )
    );
    this.properties = properties;
  }

  private calculateEdgeBuffer(width: number, height: number) {
    return Math.min(width, height) * 0.05;
  }

  private adjustCoordinates(
    x: number,
    y: number,
    xOffset: number,
    yOffset: number
  ): Coordinates {
    return [
      Math.abs(x + this.edgeBuffer / 2 + xOffset / 2),
      Math.abs(y + this.edgeBuffer / 2 + yOffset / 2),
    ];
  }

  public adjustDimensions(
    width: number,
    height: number,
    widthOffset: number,
    heightOffset: number
  ): Dimensions {
    return [
      width - this.edgeBuffer - widthOffset / 2,
      height - this.edgeBuffer - heightOffset / 2,
    ];
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
