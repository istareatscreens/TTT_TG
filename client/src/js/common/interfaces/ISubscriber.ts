export default interface ISubscriber {
  update: () => void;
  getSubscriberId: () => string;
}
