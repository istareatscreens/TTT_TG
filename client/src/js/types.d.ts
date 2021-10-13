import { Mark } from "./common/enums";

export type Dimensions = [width: number, height: number];
export type Coordinates = [x: number, y: number];
export type GameCommand = "makeMove" | "joinGame" | "joinLobby";

export type GameName = "TicTacToe";

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

export interface Game {}

export type GameProperties = [
  dimensions: Dimensions,
  lineStroke: number,
  canvas: HTMLCanvasElement
];

/*
export interface MessageIn {
  status: GameStatus;
  state: number;
  gameId: string;
  winner: Mark;
}

export interface MessageOut {
  type: PlayerOptions;
  gameId: number;
  quadrant: QuadrantNumber;
}
*/
