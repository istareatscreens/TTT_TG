export default interface ISubscriber {
  update: () => void;
  getSubscriberId?: () => string;
  getSubscriberElement?: () => HTMLElement;
}
