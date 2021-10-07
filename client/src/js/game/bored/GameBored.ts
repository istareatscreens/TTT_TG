import Grid from "./Grid";
import Quadrant from "./Quadrant";
import { Coordinates, Dimensions, QuadrantNumber } from "../../types";
import { Mark } from "../../common/enums";
import GameOverScreen from "./GameOverScreen";

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
  private gameOverState: number;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions,
    lineStroke: number
  ) {
    this.lineStroke = lineStroke;
    this.context = context;
    this.dimensions = dimensions;
    this.gameOverState = 0;

    this.grid = new Grid(context, lineStroke, dimensions);
    const [width, height] = dimensions;
    this.quadrantDimensions = [width / 3, height / 3];
    this.quadrants = [];
  }

  public draw(state: number = 0): void {
    this.context.clearRect(0, 0, ...this.dimensions);
    this.grid.draw();
    this.drawMarks(state);
    if (this.gameOverState) {
      this.drawGameOverState(this.gameOverState);
    }
  }

  private drawMarks(state: number): void {
    this.iterateOverState(state, this.markQuadrant.bind(this));
  }

  private iterateOverState(
    state: number,
    callback: (x: number, y: number, mark: Mark) => void
  ): void {
    /* 
      each two bits represents a quadrant
      00 === empty
      01 === X
      10 === O
    */
    for (let y = 0; y < 3; y++) {
      for (let x = 0; x < 3; x++) {
        callback(x, y, 3 & state);
        state >>>= 2;
      }
    }
  }

  public setGameOverState(gameOverState: number): void {
    this.gameOverState = gameOverState;
  }

  private drawGameOverState(gameOverState: number): void {
    const quadrants: QuadrantNumber[] = [];
    const getQuadrants =
      (quadrants: QuadrantNumber[]) => (x: number, y: number, mark: Mark) => {
        if (mark !== Mark.Empty) {
          quadrants.push(this.calculateQuadrantNumber(x, y));
        }
      };
    this.iterateOverState(gameOverState, getQuadrants(quadrants));

    const coordinates: Coordinates[] = [];
    quadrants.forEach((quadrant) =>
      coordinates.unshift(this.quadrants[quadrant].getCenterCoordinate())
    );

    new GameOverScreen(
      this.dimensions,
      this.lineStroke,
      this.context,
      coordinates
    ).draw();
  }

  private calculateQuadrantNumber(x: number, y: number): QuadrantNumber {
    return (y * 3 + x) as QuadrantNumber;
  }

  private markQuadrant(
    xPosition: number,
    yPosition: number,
    mark: Mark // value must be < 3
  ): void {
    const [width, height] = this.dimensions;

    const quadrantCoordinates: Coordinates = [
      ((2 - (xPosition % 3)) * width) / 3,
      ((2 - (yPosition % 3)) * height) / 3,
    ];

    const quadrantMark = new Quadrant(
      this.context,
      {
        coordinates: quadrantCoordinates,
        dimensions: this.quadrantDimensions,
        mark: mark,
        quadrant: this.calculateQuadrantNumber(xPosition, yPosition),
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

  public getQuadrantNumber(coordinates: Coordinates): QuadrantNumber {
    return this.findQuadrant(coordinates)?.getNumber() ?? null;
  }
}
