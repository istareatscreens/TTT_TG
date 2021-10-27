import { Mark } from "../../../common/enums";
import { Coordinates, QuadrantNumber } from "../../../types";
import { Content } from "../state/IGameState";
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

  private getMoveNumber(): number {
    try {
      return parseInt(
        this.properties?.moveNumbers?.[this.properties.quadrant] ?? "-1"
      );
    } catch (Exception) {
      return -1;
    }
  }

  public draw(): void {
    const { X, O, Empty } = Mark;
    console.log(
      "Quadrant: ",
      this.properties.quadrant,
      "Content: ",
      this.properties.content,
      "move Number",
      this.getMoveNumber()
    );

    if (typeof this.properties.content == "object") {
      return;
    }

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

  private drawMark(mark: string, number: number): void {
    console.log("Mark: ", mark, "Number: ", number);
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;

    const font = "Press Start 2P";

    const fontSize = Math.abs(
      Math.min(width, height) - this.edgeBuffer * (number == -1 ? 1 : 1.5)
    );
    const text = `${fontSize}px '${font}'`;
    this.context.restore();
    this.context.font = text;

    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(mark, x + width / 1.8, y + height / 1.8);

    // Number
    if (number === -1) {
      return;
    }

    const font2 = "Helvetica";
    const text2 = `bold ${fontSize / 2.8}px '${font2}'`;
    this.context.font = text2;
    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(
      `${number}`,
      x + width / 2 + fontSize / 1.45,
      y + height / 1.2
    );
  }

  private drawMarkOld(mark: string, number: string): void {
    console.log("Mark: ", mark, "Number: ", number);
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;

    const font = "Press Start 2P";

    const fontSize = Math.abs(Math.min(width, height) - this.edgeBuffer * 1.5);
    const text = `${fontSize}px '${font}'`;
    this.context.restore();
    this.context.font = text;

    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(mark, x + width / 1.8, y + height / 1.8);

    // Number
    if (parseInt(number) === -1) {
      return;
    }

    const font2 = "Helvetica";
    const text2 = `${fontSize / 1.5}px '${font2}'`;
    this.context.font = text2;
    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(
      number,
      x + width / 2 + fontSize / 1.8,
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

  public getContent(): Content {
    return this.properties.content;
  }

  public getNumber(): QuadrantNumber {
    return this.properties.quadrant;
  }
}
