import ISubscriber from "./ISubscriber";

export default interface IPublisher {
  add?: (subscriber: ISubscriber) => void;
  remove?: (subscriber: ISubscriber) => void;
  //notify?: () => void;
}
