import {
  Coordinates,
  Dimensions,
  QuadrantLocation,
  QuadrantNumber,
} from "../../../types";
import { Content } from "../state/IGameState";

export default interface IQuadrant {
  draw: () => void;
  getCenterCoordinate: () => Coordinates;
  isInQuadrant: (xCoordinate: number, yCoordinate: number) => boolean;
  isEmpty: (coordinates?: Coordinates) => boolean;
  getNumber: (coordinates?: Coordinates) => QuadrantLocation;
  isLocked?: () => boolean;
}

export interface QuadrantProperties {
  gridOffset: Dimensions;
  coordinates: Coordinates;
  dimensions: Dimensions;
  content: Content;
  quadrant: QuadrantNumber;
  moveNumbers?: string[];
  locked?: boolean;
}
