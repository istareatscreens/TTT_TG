import { Mark } from "../../../common/enums";
import {
  Content,
  Coordinates,
  Dimensions,
  QuadrantNumber,
} from "../../../types";
import TicTacToe from "../../TicTacToe";
import GameBored from "../GameBoard";

export default interface IQuadrant {
  draw: () => void;
  getCenterCoordinate: () => Coordinates;
  isInQuadrant: (xCoordinate: number, yCoordinate: number) => boolean;
  isEmpty: (() => boolean) | ((coordinates: Coordinates) => boolean);
  getNumber: () => QuadrantNumber;
}

export interface QuadrantProperties {
  coordinates: Coordinates;
  dimensions: Dimensions;
  content: Content;
  quadrant: QuadrantNumber;
}
