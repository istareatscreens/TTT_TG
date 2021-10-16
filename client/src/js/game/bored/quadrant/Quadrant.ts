import { Mark } from "../../../common/enums";
import { Coordinates, QuadrantNumber } from "../../../types";
import IQuadrant, { QuadrantProperties } from "./IQuadrant";

export default class Quadrant implements IQuadrant {
  private context: CanvasRenderingContext2D;
  private properties: QuadrantProperties;
  private lineStroke: number;
  private color: string;
  private edgeBuffer: number;

  public constructor(
    context: CanvasRenderingContext2D,
    lineStroke: number,
    properties: QuadrantProperties,
    color: string = "black"
  ) {
    this.context = context;
    this.lineStroke = lineStroke;
    this.properties = properties;
    this.color = color;

    this.edgeBuffer = this.lineStroke * 3;
  }

  public draw(): void {
    const { X, O, Empty } = Mark;
    switch (this.properties.content) {
      case X:
        this.drawX();
        break;
      case O:
        this.drawO();
        break;
      case Empty:
        //do nothing
        break;
      default:
        console.error("Invalid input");
    }
  }

  public getCenterCoordinate(): Coordinates {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    return [x + width / 2, y + height / 2];
  }

  private drawX(): void {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;

    this.drawLine(
      [x + this.edgeBuffer, y + this.edgeBuffer],
      [width + x - this.edgeBuffer, Math.abs(height + y - this.edgeBuffer)]
    );
    this.drawLine(
      [x + width - this.edgeBuffer, y + this.edgeBuffer],
      [x + this.edgeBuffer, Math.abs(y + height - this.edgeBuffer)]
    );
  }

  private drawLine(start: Coordinates, end: Coordinates): void {
    this.context.strokeStyle = this.color;
    this.context.lineWidth = this.lineStroke;
    this.context.beginPath();
    this.context.moveTo(...start);
    this.context.lineTo(...end);
    this.context.closePath();
    this.context.stroke();
  }

  private drawO(): void {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    this.context.strokeStyle = this.color;
    this.context.lineWidth = this.lineStroke;
    this.context.beginPath();
    this.context.arc(
      x + width / 2,
      y + height / 2,
      Math.abs(Math.min(width, height) / 2 - this.edgeBuffer),
      0,
      2 * Math.PI
    );
    this.context.closePath();
    this.context.stroke();
  }

  public isInQuadrant(xCoordinate: number, yCoordinate: number): boolean {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    return (
      xCoordinate > x &&
      xCoordinate < x + width &&
      yCoordinate > y &&
      yCoordinate < y + height
    );
  }

  public isEmpty(coordinates: Coordinates): boolean {
    return !(
      Mark.X === this.properties.content || this.properties.content === Mark.O
    );
  }

  public getNumber(): QuadrantNumber {
    return this.properties.quadrant;
  }
}
