import { Mark } from "../common/enums";
import {
  Coordinates,
  Dimensions,
  QuadrantLocation,
  QuadrantNumber,
} from "../types";
import { States } from "./bored/state/IGameState";

export default interface IGame {
  reset: () => void;
  getTurn: () => Mark;
  setWinner: (winner: Mark) => void;
  getWinner: () => Mark;
  setMark: (mark: Mark) => void;
  getMark: () => Mark;
  isTurn: () => boolean;
  setTurn: (turn: Mark) => void;
  setDimensions: (dimensions: Dimensions) => void;
  clearGame: () => void;
  getQuadrantNumber: (
    coordinates: Coordinates
  ) => QuadrantNumber | QuadrantLocation;
  setState: (state: States) => void;
  setGameOverState: (gameOverState: number) => void;
  draw: () => void;
}
