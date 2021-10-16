import Grid from "./Grid";
import Quadrant from "./quadrant/Quadrant";
import { Content, Coordinates, Dimensions, QuadrantNumber } from "../../types";
import { Mark } from "../../common/enums";
import IGameState from "./state/IGameState";
import TicTacToeState from "./state/TicTacToeState";

//debug function
function dec2bin(dec: number) {
  return (dec >>> 0).toString(2);
}

export default class GameBoard {
  private grid: Grid;
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private lineStroke: number;
  private quadrantDimensions: Dimensions;
  private quadrants: Array<Quadrant>;
  private gameOverState: IGameState; //place in a marked state object
  private coordinates: Coordinates;

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions,
    lineStroke: number,
    coordinates: Coordinates = [0, 0]
  ) {
    this.lineStroke = lineStroke;
    this.context = context;
    this.dimensions = dimensions;
    this.coordinates = coordinates;
    this.gameOverState = new TicTacToeState();
    this.context.globalAlpha = 1;

    this.grid = new Grid(context, lineStroke, dimensions, coordinates);
    const [width, height] = dimensions;
    this.quadrantDimensions = [width / 3, height / 3];
    this.quadrants = [];
  }

  public draw(state: IGameState): void {
    const [x, y] = this.coordinates;
    this.context.clearRect(x, y, ...this.dimensions);
    this.grid.draw();
    this.drawMarks(state);
    if (this.gameOverState.getState()) {
      this.drawGameOverState(this.gameOverState);
    }
  }

  private drawMarks(state: IGameState): void {
    this.iterateOverState(state, this.markQuadrant.bind(this));
  }

  private iterateOverState(
    state: IGameState,
    callback: (x: number, y: number, content: Content) => void
  ): void {
    /* 
      each two bits represents a quadrant
      00 === empty
      01 === X
      10 === O
    */
    for (let y = 0; y < 3; y++) {
      for (let x = 0; x < 3; x++) {
        //abstract the 3 & state, state >>>=2; !!!
        callback(x, y, state.iterate());
      }
    }
  }

  public setGameOverState(gameOverState: IGameState): void {
    this.gameOverState = gameOverState;
  }

  public drawWinningLine(coordinates: Coordinates[]) {
    let [x1, y1] = coordinates[0];
    let [x3, y3] = coordinates[2];
    const [width, height] = this.dimensions;

    const edgeBuffer = this.lineStroke * 2;

    const m = (x3 - x1) / (y3 - y1);

    if (y1 !== y3) {
      const yAdjustment = Math.abs(height / 6 - edgeBuffer);
      y1 -= m > 0 ? yAdjustment : yAdjustment;
      y3 += m > 0 ? yAdjustment : yAdjustment;
    }
    if (x1 !== x3) {
      const xAdjustment = Math.abs(width / 6 - edgeBuffer);
      x1 -= m > 0 ? xAdjustment : -xAdjustment;
      x3 += m > 0 ? xAdjustment : -xAdjustment;
    }

    // Draw line
    this.context.lineWidth = this.lineStroke * 1.5;
    this.context.strokeStyle = "#92140c";
    this.context.beginPath();
    this.context.moveTo(...this.adjustCoordinatesWithOffset(x1, y1));
    this.context.lineTo(...this.adjustCoordinatesWithOffset(x3, y3));
    this.context.stroke();
  }

  private adjustCoordinatesWithOffset(x: number, y: number): Coordinates {
    const [xOffset, yOffset] = this.coordinates;
    return [x + xOffset, y + yOffset];
  }

  // create a quadrant factory object
  private drawGameOverState(gameOverState: IGameState): void {
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

    this.drawWinningLine(coordinates);
  }

  private calculateQuadrantNumber(x: number, y: number): QuadrantNumber {
    return (y * 3 + x) as QuadrantNumber;
  }

  private markQuadrant(
    xPosition: number,
    yPosition: number,
    content: Content // value must be < 3
  ): void {
    const [width, height] = this.dimensions;

    const quadrantCoordinates: Coordinates = [
      ((2 - (xPosition % 3)) * width) / 3,
      ((2 - (yPosition % 3)) * height) / 3,
    ];

    const quadrantMark = new Quadrant(this.context, this.lineStroke, {
      coordinates: this.adjustCoordinatesWithOffset(...quadrantCoordinates),
      dimensions: this.quadrantDimensions,
      content: content,
      quadrant: this.calculateQuadrantNumber(xPosition, yPosition),
    });

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
