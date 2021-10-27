import { Mark } from "../../common/enums";
import {
  GameCommand,
  GameName,
  GameResponse,
  QuadrantNumber,
  QuadrantPosition,
} from "../../types";
import { States } from "../bored/state/IGameState";

export interface IMessageIn {}
export interface IMessageOut {}

export interface TTTMessageOut extends IMessageOut {
  type: GameCommand;
  game: GameName;
  gameId: string;
  position: QuadrantNumber | QuadrantPosition;
}

export interface TTTMessageIn extends IMessageIn {
  status: GameResponse;
  gameId: string;
  state: States;
  playerNumber: Mark;
  winner: Mark;
  gameOverState: number;
  turn: Mark;
}
