import { Coordinates, Dimensions, QuadrantNumber } from "../../../types";
import { Content } from "../state/IGameState";

export default interface IQuadrant {
  draw: () => void;
  getCenterCoordinate: () => Coordinates;
  isInQuadrant: (xCoordinate: number, yCoordinate: number) => boolean;
  isEmpty: (coordinates: Coordinates) => boolean;
  getNumber: () => QuadrantNumber;
}

export interface QuadrantProperties {
  gridOffset: Dimensions;
  coordinates: Coordinates;
  dimensions: Dimensions;
  content: Content;
  moveNumbers?: string[];
  quadrant: QuadrantNumber;
}
