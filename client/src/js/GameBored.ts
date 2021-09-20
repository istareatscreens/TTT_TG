import Grid from "./Grid";
import Quadrant, { Mark } from "./Quadrant";
import { Coordinates, Dimensions } from "./types";

//debug function
function dec2bin(dec: number) {
  return (dec >>> 0).toString(2);
}

export default class GameBored {
  private grid: Grid;
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private lineStroke: number;
  private quadrantDimensions: Dimensions;
  private quadrants: Array<Quadrant>;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions,
    lineStroke: number
  ) {
    this.lineStroke = lineStroke;
    this.context = context;
    this.dimensions = dimensions;
    this.grid = new Grid(context, lineStroke, dimensions);
    const [width, height] = dimensions;
    this.quadrantDimensions = [width / 3, height / 3];
    this.quadrants = [];
  }

  public draw(state: number = 0): void {
    this.context.clearRect(0, 0, ...this.dimensions);
    this.grid.draw();
    this.drawMarks(state);
  }

  private drawMarks(state: number): void {
    /* 
      each two bits represents a quadrant
      00 === empty
      01 === X
      10 === O
    */
    for (let y = 0; y < 3; y++) {
      for (let x = 0; x < 3; x++) {
        this.markQuadrant(x, y, 3 & state);
        state >>>= 2;
      }
    }
  }

  private markQuadrant(
    xPosition: number,
    yPosition: number,
    mark: Mark // value must be < 3
  ): void {
    const [width, height] = this.dimensions;

    const quadrantCoordinates: Coordinates = [
      ((xPosition % 3) * width) / 3,
      ((yPosition % 3) * height) / 3,
    ];

    const quadrantMark = new Quadrant(
      this.context,
      {
        coordinates: quadrantCoordinates,
        dimensions: this.quadrantDimensions,
        mark: mark,
        quadrant: yPosition * 3 + xPosition,
      },
      this.lineStroke
    );

    quadrantMark.draw();

    this.quadrants.push(quadrantMark);
  }

  public isValidMove(coordinates: Coordinates): boolean {
    return (
      (!this.grid.isInsideGrid(...coordinates) &&
        this.findQuadrant(coordinates)?.isEmpty()) ??
      false
    );
  }
  private findQuadrant(coordinates: Coordinates): Quadrant | undefined {
    return this.quadrants.find(
      (quadrant) =>
        !this.grid.isInsideGrid(...coordinates) &&
        quadrant.isInQuadrant(...coordinates)
    );
  }

  public getQuadrantNumber(coordinates: Coordinates): number | undefined {
    return this.findQuadrant(coordinates)?.getNumber();
  }
}
