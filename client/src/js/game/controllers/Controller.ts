import IPublisher from "../../common/interfaces/IPublisher";
import ISubscriber from "../../common/interfaces/ISubscriber";
import { Coordinates, Dimensions } from "../../types";

export default interface Controller extends IPublisher {
  hasCoordinates?: (subscriber: ISubscriber) => boolean;
  getCoordinates?: (subscriber: ISubscriber) => Coordinates;
  hasDimensions?: () => boolean;
  getDimensions?: () => Dimensions;
  connect: () => void;
}
