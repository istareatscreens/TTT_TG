import { Coordinates, Dimensions, QuadrantNumber } from "../../types";

export default class GameOverScreen {
  private context: CanvasRenderingContext2D;
  private lineStroke: number;
  private dimensions: Dimensions;
  private coordinates: Coordinates[];

  public constructor(
    dimensions: Dimensions,
    lineStroke: number,
    context: CanvasRenderingContext2D,
    coordinates: Coordinates[]
  ) {
    this.coordinates = coordinates;
    this.context = context;
    this.dimensions = dimensions;
    this.lineStroke = lineStroke;
  }

  public clear() {}

  public draw() {
    this.drawWinningDash();
  }

  public drawGameOverTextBox() {}

  public drawWinningDash() {
    let [x1, y1] = this.coordinates[0];
    let [x3, y3] = this.coordinates[2];
    const [width, height] = this.dimensions;

    const edgeBuffer = this.lineStroke * 2;
    if (y1 !== y3) {
      y1 -= Math.abs(height / 6 - edgeBuffer);
      y3 += Math.abs(height / 6 - edgeBuffer);
    }
    if (x1 !== x3) {
      x1 -= Math.abs(width / 6 - edgeBuffer);
      x3 += Math.abs(width / 6 - edgeBuffer);
    }

    // Draw line
    this.context.lineWidth = this.lineStroke * 1.5;
    this.context.strokeStyle = "#ff0000";
    this.context.beginPath();
    this.context.moveTo(x1, y1);
    this.context.lineTo(x3, y3);
    this.context.stroke();
  }
}
