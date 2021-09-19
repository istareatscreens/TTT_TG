import Grid from "./Grid";
import QuadrantMark, { Mark } from "./QuadrantMark";
import { Coordinates, Dimensions } from "./types";

function dec2bin(dec: number) {
  return (dec >>> 0).toString(2);
}

export default class GameBored {
  private grid: Grid;
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private lineStroke: number;
  private quadrantDimensions: Dimensions;
  private marks: Array<QuadrantMark>;

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
    this.marks = [];
  }

  public draw(state: number): void {
    this.grid.draw();
    this.update(state);
  }

  public update(state: number): void {
    console.log(dec2bin(state));
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
    console.log(dec2bin(mark));

    const [width, height] = this.dimensions;
    const lineStrokeAdjustment = (coordinate: number): number =>
      coordinate % 3 === 0 ? 0 : this.lineStroke;

    const quadrantCoordinates: Coordinates = [
      ((xPosition % 3) * width) / 3 + lineStrokeAdjustment(xPosition),
      ((yPosition % 3) * height) / 3 + lineStrokeAdjustment(yPosition),
    ];

    const quadrantMark = new QuadrantMark(
      this.context,
      {
        coordinates: quadrantCoordinates,
        dimensions: this.quadrantDimensions,
        mark: mark,
      },
      this.lineStroke
    );

    quadrantMark.draw();

    this.marks.push(quadrantMark);
  }
}
