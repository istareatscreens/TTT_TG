import { Mark } from "../common/enums";

export type Dimensions = [width: number, height: number];
export type Coordinates = [x: number, y: number];
export type GameCommand = "makeMove" | "joinGame" | "joinLobby";

export type GameName = "TicTacToe" | "QTicTacToe";

export type GameResponse =
  | "initial"
  | "gameOver"
  | "inGame"
  | "inLobby"
  | "playerRejoin"
  | "playerLeft"
  | "invalidGame"
  | "gameOver";

export type QuadrantNumber = null | 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8;
export type QuadrantCoordinate = [QuadrantNumber, QuadrantNumber];

export type QuadrantLocation = QuadrantNumber | QuadrantCoordinate;

export interface Game {}

export type GameProperties = [
  dimensions: Dimensions,
  lineStroke: number,
  canvas: HTMLCanvasElement
];

// not used
export type QuadrantType = "TicTacToe" | "Mark";

export type QuadrantPosition =
  | [board: QuadrantNumber, quadrant: QuadrantNumber]
  | null;
