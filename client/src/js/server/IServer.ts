import IPublisher from "../common/interfaces/IPublisher";
import { TTTMessageIn, TTTMessageOut } from "../game/message/TicTacToeMessage";

export default interface IServer extends IPublisher {
  getMessageIn: () => TTTMessageIn;
  hasMessageIn: () => boolean;
  start: () => void;
  isConnected: () => boolean;
  send: (msg: TTTMessageOut) => void;
}
