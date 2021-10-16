import { Content, States } from "../../../types";

export default interface IGameState {
  setState: (state: States) => void;
  getState: () => number | States;
  iterate: () => Content;
}
