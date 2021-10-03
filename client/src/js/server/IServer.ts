import IPublisher from "../common/interfaces/IPublisher";
import { MessageOut, MessageIn } from "../types";

export default interface IServer extends IPublisher {
  getMessageIn: () => MessageIn;
  hasMessageIn: () => boolean;
  start: () => void;
  isConnected: () => boolean;
  send: (msg: MessageOut) => void;
}
