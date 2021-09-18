import { Dimensions } from "./types";
export default class Grid {
  private dimensions: Dimensions;
  private lineStroke: number;
  private context: CanvasRenderingContext2D;
  private color: string;

  constructor(
    context: CanvasRenderingContext2D,
    lineStroke: number,
    dimensions: { width: number; height: number },
    color: string = "black"
  ) {
    this.dimensions = dimensions;
    this.context = context;
    this.lineStroke = lineStroke;
    this.color = color;
  }

  public draw(): void {
    const { width, height } = this.dimensions;
    this.createLine(width / 3, 0, this.lineStroke, height);
    this.createLine((width * 2) / 3, 0, this.lineStroke, height);
    this.createLine(0, height / 3, width, this.lineStroke);
    this.createLine(0, (height * 2) / 3, width, this.lineStroke);
  }

  private createLine(
    x: number,
    y: number,
    width: number,
    height: number
  ): void {
    this.context.fillStyle = this.color;
    this.context.fill();
    this.context.fillRect(x, y, width, height);
  }

  // prettier-ignore
  public isInsideGrid(x: number, y: number): boolean {
    const { width, height } = this.dimensions;
    return (
      (x > width / 3 && x < width / 3 + this.lineStroke && y > 0 && y < height) ||
      (x > width*2 / 3 && x < width*2 / 3 + this.lineStroke && y > 0 && y < height) ||
      (x > 0 && x < width  && y > height/3 && y < height/3 + this.lineStroke) ||
      (x > 0 && x < width  && y > height*2/3 && y < height*2/3 + this.lineStroke)
    );

  }
}
