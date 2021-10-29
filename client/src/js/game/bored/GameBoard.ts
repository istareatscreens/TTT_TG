import Grid from "./Grid";
import {
  Coordinates,
  Dimensions,
  QuadrantLocation,
  QuadrantNumber,
} from "../../types/game";
import { Mark } from "../../common/enums";
import IGameState, { Content } from "./state/IGameState";
import TicTacToeState from "./state/TicTacToeState";
import QuadrantFactory from "./quadrant/QuadrantFactory";
import IQuadrant from "./quadrant/IQuadrant";

//debug function
function dec2bin(dec: number) {
  return (dec >>> 0).toString(2);
}

type IsValidQuadrant = (
  initialMark: Content,
  mark: Content,
  index: number
) => boolean;

interface BoardProperties {
  gridColor: string;
  markColor: string;
  lineStroke: number;
  gridStroke: number;
  moveNumbers?: string[];
}

export default class GameBoard {
  private grid: Grid;
  private context: CanvasRenderingContext2D;
  private dimensions: Dimensions;
  private lineStroke: number;
  private quadrantDimensions: Dimensions;
  private quadrants: Array<IQuadrant>;
  private gameOverState: IGameState; //place in a marked state object
  private coordinates: Coordinates;
  private quadrantFactory: QuadrantFactory;
  private gridStroke: number;
  private moveNumbers: string[];

  public constructor(
    context: CanvasRenderingContext2D,
    dimensions: Dimensions,
    properties: BoardProperties,
    coordinates: Coordinates = [0, 0]
  ) {
    this.lineStroke = properties.lineStroke;
    this.moveNumbers = properties.moveNumbers ?? [];
    this.context = context;
    this.dimensions = dimensions;
    this.coordinates = coordinates;
    this.gameOverState = new TicTacToeState();
    this.context.globalAlpha = 1;
    this.gridStroke = properties.gridStroke;

    this.quadrantFactory = new QuadrantFactory(context, this.lineStroke);
    this.grid = new Grid(
      context,
      this.gridStroke,
      dimensions,
      coordinates,
      properties.gridColor
    );
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
    console.log("HERE in draw function is being called", coordinates);
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

  private drawHorizontalWinningLines(isValidQuadrant: IsValidQuadrant): void {
    for (let i = 0; i < 3; i++) {
      let line: Coordinates[] = [];
      // winning horizontal line draw
      const getMark = (index: QuadrantNumber): Content =>
        this.quadrants?.[index]?.getContent?.() ?? 0;
      let mark = getMark((3 * i) as QuadrantNumber);
      let initialMark = mark;
      for (let j = 3 * i; j < 3 + 3 * i; j++) {
        let mark = getMark((3 * i) as QuadrantNumber);
        if (isValidQuadrant(initialMark, mark, j)) {
          line = [];
          break;
        }
        line.unshift(this.quadrants[j].getCenterCoordinate());
      }
      if (line.length === 3) {
        this.drawWinningLine(line);
      }
    }
  }

  private drawVeritcleWinningLines(isValidQuadrant: IsValidQuadrant): void {
    for (let i = 0; i < 3; i++) {
      let line: Coordinates[] = [];
      const getMark = (index: QuadrantNumber): Content =>
        this.quadrants?.[index]?.getContent?.() ?? 0;
      let mark = getMark(i as QuadrantNumber);
      let initialMark = mark;
      for (let j = i; j < 9; j += 3) {
        mark = getMark(i as QuadrantNumber);
        if (isValidQuadrant(initialMark, mark, j)) {
          line = [];
          break;
        }
        line.unshift(this.quadrants[j].getCenterCoordinate());
      }
      if (line.length === 3) {
        this.drawWinningLine(line);
      }
    }
  }

  private drawBackwardSlashLine(isValidQuadrant: IsValidQuadrant): void {
    let line: Coordinates[] = [];
    const getMark = (index: QuadrantNumber): Content =>
      this.quadrants?.[index]?.getContent?.() ?? 0;
    let mark = getMark(0);
    const initialMark = mark;
    for (let j = 0; j < 9; j += 4) {
      mark = getMark(j as QuadrantNumber);
      if (isValidQuadrant(initialMark, mark, j)) {
        break;
      }
      line.unshift(this.quadrants[j].getCenterCoordinate());
    }
    if (line.length === 3) {
      this.drawWinningLine(line);
    }
  }

  private drawFowardSlashLine(isValidQuadrant: IsValidQuadrant): void {
    const line: Coordinates[] = [];
    const getMark = (index: QuadrantNumber): Content =>
      this.quadrants?.[index]?.getContent?.() ?? 0;
    let mark = getMark(2);
    const initialMark = mark;
    for (let j = 2; j < 7; j += 2) {
      mark = getMark(j as QuadrantNumber);
      if (isValidQuadrant(initialMark, mark, j)) {
        break;
      }
      line.unshift(this.quadrants[j].getCenterCoordinate());
    }
    if (line.length === 3) {
      this.drawWinningLine(line);
    }
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

    const isValidQuadrant = (
      initialMark: Content,
      mark: Content,
      index: number
    ) => {
      return (
        !quadrants.includes(index as QuadrantNumber) ||
        mark !== initialMark ||
        mark === 0
      );
    };

    this.drawBackwardSlashLine(isValidQuadrant);
    this.drawFowardSlashLine(isValidQuadrant);
    this.drawHorizontalWinningLines(isValidQuadrant);
    this.drawVeritcleWinningLines(isValidQuadrant);
  }

  private calculateQuadrantNumber(x: number, y: number): QuadrantNumber {
    return (y * 3 + x) as QuadrantNumber;
  }

  private markQuadrant(
    xPosition: number,
    yPosition: number,
    content: Content
  ): void {
    const [width, height] = this.dimensions;

    const quadrantCoordinates: Coordinates = [
      ((2 - (xPosition % 3)) * width) / 3,
      ((2 - (yPosition % 3)) * height) / 3,
    ];

    const properties = {
      gridOffset: [this.gridStroke, this.gridStroke] as Coordinates,
      coordinates: this.adjustCoordinatesWithOffset(...quadrantCoordinates),
      dimensions: this.quadrantDimensions,
      content: content,
      quadrant: this.calculateQuadrantNumber(xPosition, yPosition),
      moveNumbers: this.moveNumbers,
    };

    const quadrant = this.quadrantFactory.createQuadrant(properties);
    quadrant.draw();

    this.quadrants.push(quadrant);
  }

  public isValidMove(coordinates: Coordinates): boolean {
    return (
      (!this.grid.isInsideGrid(...coordinates) &&
        this.findQuadrant(coordinates)?.isEmpty(coordinates)) ??
      false
    );
  }

  private findQuadrant(coordinates: Coordinates): IQuadrant | undefined {
    return this.quadrants.find(
      (quadrant) =>
        !this.grid.isInsideGrid(...coordinates) &&
        quadrant.isInQuadrant?.(...coordinates)
    );
  }

  public getQuadrantNumber(coordinates: Coordinates): QuadrantLocation {
    return this.findQuadrant(coordinates)?.getNumber(coordinates) ?? null;
  }
}
