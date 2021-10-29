import { Coordinates, Dimensions } from "../../types/game";
export default class Grid {
  private dimensions: Dimensions;
  private lineStroke: number;
  private context: CanvasRenderingContext2D;
  private color: string;
  private coordinates: Coordinates;

  public constructor(
    context: CanvasRenderingContext2D,
    lineStroke: number,
    dimensions: Dimensions,
    coordinates: Coordinates,
    color: string = "black"
  ) {
    this.coordinates = coordinates;
    this.dimensions = dimensions;
    this.context = context;
    this.lineStroke = lineStroke / 2;
    this.color = color;
  }

  private adjustCoordinatesByOffset(x: number, y: number): Coordinates {
    const [xOffset, yOffset] = this.coordinates;
    return [x + xOffset, y + yOffset];
  }

  public draw(): void {
    const [width, height] = this.dimensions;
    this.createLine(
      ...this.adjustCoordinatesByOffset(width / 3, 0),
      this.lineStroke,
      height
    );
    this.createLine(
      ...this.adjustCoordinatesByOffset((width * 2) / 3, 0),
      this.lineStroke,
      height
    );
    this.createLine(
      ...this.adjustCoordinatesByOffset(0, height / 3),
      width,
      this.lineStroke
    );
    this.createLine(
      ...this.adjustCoordinatesByOffset(0, (height * 2) / 3),
      width,
      this.lineStroke
    );
  }

  private createLine(
    x: number,
    y: number,
    width: number,
    height: number
  ): void {
    this.context.fillStyle = this.color;
    this.context.fillRect(x, y, width, height);
  }

  // prettier-ignore
  public isInsideGrid(x: number, y: number): boolean {
    const [width, height] = this.dimensions;
    const [xOffset, yOffset] = this.coordinates;
    

    return (
      (x > width / 3 + xOffset && x < width / 3 + xOffset + this.lineStroke && y > 0 + yOffset && y < height + yOffset) ||
      (x > width * 2 / 3 + xOffset && x < width * 2 / 3 + xOffset + this.lineStroke && y > 0 + yOffset && y < height + yOffset) ||
      (x > 0 + xOffset && x < width + xOffset  && y > height / 3 + yOffset && y < height / 3 + yOffset + this.lineStroke) ||
      (x > 0 + xOffset && x < width + xOffset  && y > height * 2 / 3 + yOffset && y < height * 2 / 3 + yOffset + this.lineStroke)
    );
  }
}
