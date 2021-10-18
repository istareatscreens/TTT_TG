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
    this.lineStroke =
      Math.min(properties.dimensions[0], properties.dimensions[1]) * 0.1; //lineStroke;
    this.properties = properties;
    this.color = color;
    this.edgeBuffer = this.lineStroke * 3;
  }

  private getMoveNumber(): string {
    return this.properties?.moveNumbers?.[this.properties.quadrant] ?? "";
  }

  public draw(): void {
    const { X, O, Empty } = Mark;
    switch (this.properties.content) {
      case X:
        this.drawMark("X", this.getMoveNumber());
        break;
      case O:
        this.drawMark("O", this.getMoveNumber());
        break;
      case Empty:
        //do nothing
        break;
      default:
        console.error("Invalid input in Quadrant");
    }
  }

  public getCenterCoordinate(): Coordinates {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    return [x + width / 2, y + height / 2];
  }

  private drawMark(mark: string, number: string): void {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;

    const font = "Press Start 2P";

    const fontSize = Math.abs(Math.min(width, height) - this.edgeBuffer);
    const text = `${fontSize}px '${font}'`;
    this.context.restore();
    this.context.font = text;

    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText("X", x + width / 1.8, y + height / 1.8);

    // Number
    const text2 = `${fontSize / 10}px '${font}'`;
    this.context.font = text2;
    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(
      "55",
      x + width / 2 + fontSize / 1.7,
      y + height / 1.2
    );
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
