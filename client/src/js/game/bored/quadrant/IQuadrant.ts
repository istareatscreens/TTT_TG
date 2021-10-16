import {
  Content,
  Coordinates,
  Dimensions,
  QuadrantNumber,
} from "../../../types";

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
  quadrant: QuadrantNumber;
}
