import { Mark } from "../common/enums";
import { Dimensions, Coordinates, QuadrantNumber } from "../types";
import IGame from "./IGame";

class QuantumTicTacToe implements IGame {
  reset: () => void;
  getTurn: () => Mark;
  setWinner: (winner: Mark) => void;
  getWinner: () => Mark;
  setMark: (mark: Mark) => void;
  getMark: () => Mark;
  isTurn: () => boolean;
  setDimensions: (dimensions: Dimensions) => void;
  clearGame: () => void;
  getQuadrantNumber: (
    coordinates: Coordinates
  ) => [board: QuadrantNumber, quadrant: QuadrantNumber];
  setState: (state: number[]) => void;
  setGameOverState: (gameOverState: number) => void;
  draw: () => void;
}
