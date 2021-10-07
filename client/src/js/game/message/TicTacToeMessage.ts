import { Mark } from "../../common/enums";
import {
  GameCommand,
  GameName,
  GameResponse,
  QuadrantNumber,
} from "../../types";
import { IMessage, IMessageIn, IMessageOut } from "./IMessage";

export interface TTTMessageOut extends IMessageOut {
  type: GameCommand;
  game: GameName;
  gameId: string;
  playerId: string;
  position: QuadrantNumber;
}

export interface TTTMessageIn extends IMessageIn {
  status: GameResponse;
  playerId: string;
  gameId: string;
  state: number;
  playerNumber: Mark;
  winner: Mark;
  gameOverState: number;
}

export class TicTacToeMessage implements IMessage {
  public messageOut: TTTMessageOut[];
  public messageIn: TTTMessageOut[];

  public setMessageIn(message: TTTMessageOut) {}
  public getMessageIn(message: TTTMessageOut) {}
  public getMessageOut(message: TTTMessageIn) {}
  public setMessageOut(message: TTTMessageIn) {}
}
