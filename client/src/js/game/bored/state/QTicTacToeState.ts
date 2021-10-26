import IGameState, { Content, QuantumState, States } from "./IGameState";
import QuadrantIterator from "./QuadrantIterator";

export default class QTicTacToeState implements IGameState {
  private states: QuantumState;
  private iterator: QuadrantIterator;

  public constructor(
    /*
    states: QuantumState = [
      "87381,1,2,3,4,5,6,7,8,9",
      "174762,0,10,11,12,13,14,15,16,17",
      "87381,18,19,20,21,22,23,24,25,26",
      "174762,27,28,29,30,31,32,33,34,35",
      "87381,36,37,38,39,40,41,42,43,44",
      "174762,45,46,47,48,49,50,0,1,2",
      "87381,3,4,5,6,7,8,9,10,11",
      "174762,12,13,14,15,16,17,18,19,20",
      "87381,21,22,23,24,25,26,27,28,1",
    ]
    */
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
