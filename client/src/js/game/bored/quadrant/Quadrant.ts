import { Mark } from "../../../common/enums";
import { Coordinates, QuadrantNumber } from "../../../types";
import IQuadrant, { QuadrantProperties } from "./IQuadrant";

export default class Quadrant implements IQuadrant {
  private context: CanvasRenderingContext2D;
  private properties: QuadrantProperties;
  private lineStroke: number;
  private color: string;
  private edgeBuffer: number;
  private static textWidth: number;

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

  public draw(): void {
    const { X, O, Empty } = Mark;
    switch (this.properties.content) {
      case X:
        //this.drawX();
        this.drawO();
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
    /*
    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    const font = "Press Start 2P";

    const fontSize = Math.abs(Math.min(width, height) - this.edgeBuffer);
    const text = `${fontSize}px '${font}'`;

    */
    /*
    console.log(Quadrant.textWidth);
    // this api is really really bad why is this so bad
    if (!Quadrant.textWidth) {
      console.log("HERE");
      const textMetrics = this.context.measureText(text);
      console.log(textMetrics);
      Quadrant.textWidth = textMetrics.width;
    }
    console.log("Width: ", Quadrant.textWidth);

    const fontHeight = parseInt(this.context.font);
    */
    /*
    this.context.font = text;
    const { width: fontWidth, height: fontHeight } = this.measureText(
      "O",
      `${fontSize}`,
      font
    );
    console.log(this.measureText("O", `${fontSize}`, font));
    this.context.textAlign = "center";

    this.context.fillText("O", x + width / 2, y + height / 2);

    */

    const [x, y] = this.properties.coordinates;
    const [width, height] = this.properties.dimensions;
    const font = "Press Start 2P";
    const fontSize = Math.abs(Math.min(width, height) - this.edgeBuffer);
    const text = `${fontSize}px '${font}'`;

    this.context.restore();
    const fontWidth = this.context.measureText(text).width;

    if (!Quadrant.textWidth) {
      const textMetrics = this.context.measureText(text);
      Quadrant.textWidth = textMetrics.width;
    }

    this.context.font = text;
    console.log("CORDS", x, y);
    console.log("QUAD: ", this.properties.quadrant);
    console.log("Dimensions: ", width, height);
    console.log("center point: ", x + width / 2, y + height / 3);
    //console.log("font-Width", fontWidth, fontHeight);
    console.log("fontwidth: ", Quadrant.textWidth);
    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText("X", x + width / 1.8, y + height / 1.8);

    const text2 = `${fontSize / 10}px '${font}'`;
    console.log(text2);
    this.context.font = text2;
    this.context.textBaseline = "middle";
    this.context.textAlign = "center";
    this.context.fillText(
      "55",
      x + width / 2 + fontSize / 1.7,
      y + height / 1.2
    );

    /*
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
    */
  }

  public measureText(pText: string, pFontSize: string, pFontFamily: string) {
    var lDiv = document.createElement("div");

    document.body.appendChild(lDiv);

    lDiv.style.fontFamily = pFontFamily;
    lDiv.style.fontSize = "" + pFontSize + "px";
    lDiv.style.position = "absolute";
    lDiv.style.left = "-1000";
    lDiv.style.top = "-1000";

    lDiv.textContent = pText;

    var lResult = {
      width: lDiv.clientWidth,
      height: lDiv.clientHeight,
    };

    document.body.removeChild(lDiv);
    lDiv = null;

    return lResult;
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
