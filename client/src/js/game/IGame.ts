import { Mark } from "../common/enums";
import {
  Coordinates,
  Dimensions,
  GameName,
  QuadrantLocation,
  QuadrantNumber,
} from "../types";
import { States } from "./bored/state/IGameState";

export default interface IGame {
  getName: () => GameName;
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
  gameIsOver: () => boolean;
  getQuadrantNumber: (
    coordinates: Coordinates
  ) => QuadrantNumber | QuadrantLocation;
  setState: (state: States) => void;
  setGameOverState: (gameOverState: number) => void;
  draw: () => void;
}
