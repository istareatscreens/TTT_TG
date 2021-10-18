import { Mark } from "../../../common/enums";
import IGameState, { Content } from "./IGameState";
import QuadrantIterator from "./QuadrantIterator";

export default class TicTacToeState implements IGameState {
  private state: number;
  private iterator: QuadrantIterator;

  public constructor(state: number = 0) {
    this.setState(state);
    this.iterator = new QuadrantIterator();
  }

  public setState(state: number): void {
    this.state = state;
  }

  public getState(): number {
    return this.state;
  }

  public iterate(): Content {
    const mark = 3 & (this.state >>> (2 * this.iterator.getIterator()));
    this.iterator.incrementIterator();
    return mark;
  }
}
