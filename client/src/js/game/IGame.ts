import { Mark } from "../common/enums";
import {
  Coordinates,
  Dimensions,
  QuadrantNumber,
  QuadrantPosition,
  QuantumState,
} from "../types";

export default interface IGame {
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
  ) => QuadrantNumber | QuadrantPosition;
  setState: (state: number | QuantumState) => void;
  setGameOverState: (gameOverState: number) => void;
  draw: () => void;
}
