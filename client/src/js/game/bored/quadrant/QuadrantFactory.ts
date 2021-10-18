import { QuadrantProperties } from "./IQuadrant";
import Quadrant from "./Quadrant";
import QauntumQuadrant from "./QuantumQuadrant";

export default class QuadrantFactory {
  private context: CanvasRenderingContext2D;
  private lineStroke: number;
  private color: string;

  public constructor(
    context: CanvasRenderingContext2D,
    lineStroke: number,
    color: string = "black"
  ) {
    this.context = context;
    this.lineStroke = lineStroke;
    this.color = color;
  }

  private convertBoardStateToNumber(state: string): number {
    try {
      return parseInt(state.substring(1));
    } catch (e) {
      console.error(`Invalid board qState converstion: ${e}`);
      return 0;
    }
  }

  private parseTurnNumbers(state: string): (number | string[])[] {
    const [stateString, ...numbers] = state.split(",");
    return [this.convertBoardStateToNumber(stateString), numbers];
  }

  public createQuadrant(properties: QuadrantProperties) {
    if (typeof properties.content === "string") {
      //properties.content = this.convertBoardStateToNumber(properties.content);
      const [state, numbers] = this.parseTurnNumbers(properties.content);
      properties.content = state as number;
      properties.moveNumbers = numbers as string[];
      return new QauntumQuadrant(
        this.context,
        properties,
        this.lineStroke,
        this.color
      );
    } else {
      return new Quadrant(
        this.context,
        this.lineStroke,
        properties,
        this.color
      );
    }
  }
}
