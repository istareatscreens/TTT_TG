import { QuadrantNumber } from "../../../types/game";

// I know this could be better done with generators
export default class QuadrantIterator {
  private iterator: QuadrantNumber;

  public constructor() {
    this.iterator = 0;
  }

  public incrementIterator(): void {
    this.iterator = (++this.iterator % 9) as QuadrantNumber;
  }

  public getIterator(): QuadrantNumber {
    return this.iterator;
  }
}
