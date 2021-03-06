import IGameState, { Content, QuantumState, States } from "./IGameState";
import QuadrantIterator from "./QuadrantIterator";

export default class QTicTacToeState implements IGameState {
  private states: QuantumState;
  private iterator: QuadrantIterator;

  public constructor(
    states: QuantumState = [
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
      "0,-1,-1,-1,-1,-1,-1,-1,-1,-1",
    ]
  ) {
    this.setState(states);
    this.iterator = new QuadrantIterator();
  }

  public setState(states: QuantumState): void {
    this.states = states;
  }

  public getState(): States {
    return this.states;
  }

  public iterate(): Content {
    const state = this.states[this.iterator.getIterator()]; //right number in list is 0,0
    this.iterator.incrementIterator();
    return state;
  }
}
