import { Coordinates, Dimensions } from "./types";

export enum Mark {
  Empty,
  X,
  O,
}

interface MarkProperties {
  coordinates: Coordinates;
  dimensions: Dimensions;
  mark: Mark;
}

export default class QuadrantMark {
  private context: CanvasRenderingContext2D;
  private properties: MarkProperties;
  private lineStroke: number;
  private color: string;
  private edgeBuffer: number;

  public constructor(
    context: CanvasRenderingContext2D,
    properties: MarkProperties,
    lineStroke: number,
    color: string = "black"
  ) {
    this.context = context;
    this.lineStroke = lineStroke;
    this.properties = properties;
    this.color = color;
    this.edgeBuffer = this.lineStroke * 5;
  }

  public draw(): void {
    const { X, O, Empty } = Mark;
    switch (this.properties.mark) {
      case X:
        this.drawX();
        break;
      case O:
        break;
      case Empty: //do nothing
        break;
      default:
        console.log(this.properties.mark);
    }
  }

  private drawX(): void {
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;

    this.drawLine(
      [x + this.edgeBuffer, y + this.edgeBuffer],
      [width + x - this.edgeBuffer, height + y - this.edgeBuffer]
    );
    this.drawLine(
      [x + width - this.edgeBuffer, y + this.edgeBuffer],
      [x + this.edgeBuffer, y + height - this.edgeBuffer]
    );
  }

  private drawLine(start: Coordinates, end: Coordinates): void {
    this.context.strokeStyle = this.color;
    this.context.lineWidth = this.lineStroke;
    this.context.beginPath();
    this.context.moveTo(...start);
    this.context.lineTo(...end);
    this.context.stroke();
    this.context.closePath();
  }

  private drawO(): void {}
}
