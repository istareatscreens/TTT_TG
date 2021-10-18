import { Mark } from "../../../common/enums";

export default interface IGameState {
  setState: (state: States) => void;
  getState: () => number | States;
  iterate: () => Content;
}

export type QuantumState = (Mark | string)[];

export type States = number | QuantumState;
export type Content = Mark | string;
