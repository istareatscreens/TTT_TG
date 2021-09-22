import IPublisher from "../../common/interfaces/IPublisher";
import { Coordinates, Dimensions } from "../../types";

export default interface Controller extends IPublisher {
  hasCoordinates?: () => boolean;
  getCoordinates?: () => Coordinates;
  hasDimensions?: () => boolean;
  getDimensions?: () => Dimensions;
  connect: () => void;
}
