import { Mark } from "../../../common/enums";
import { Content } from "../../../types";
import IGameState from "./IGameState";
import QuadrantIterator from "./QuadrantIterator";

export default class TicTacToeState implements IGameState {
  private state: number;
  private iterator: QuadrantIterator;

  public constructor(state: number = 0) {
    this.setState(state);
    this.iterator = new QuadrantIterator();
  }

  public setState(state: number): void {
    console.log("SET STATE: ", state);
    this.state = state;
    console.log("SET STATE: ", this.state);
  }

  public getState(): number {
    console.log("SET STATE", this.state);
    return this.state;
  }

  public iterate(): Content {
    const mark = 3 & (this.state >>> (2 * this.iterator.getIterator()));
    this.iterator.incrementIterator();
    return mark;
  }
}
