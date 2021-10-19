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
      return parseInt(state);
    } catch (e) {
      console.error(`Invalid board qState converstion: ${e}`);
      return 0;
    }
  }

  private parseTurnNumbers(
    unparsedState: string
  ): (boolean | number | string[])[] {
    const [state, ...numbers] = unparsedState.split(",");
    const locked = "L" === unparsedState[0];
    return [
      locked,
      this.convertBoardStateToNumber(locked ? state.substring(1) : state),
      numbers,
    ];
  }

  public createQuadrant(properties: QuadrantProperties) {
    if (typeof properties.content === "string") {
      //properties.content = this.convertBoardStateToNumber(properties.content);
      const [locked, state, numbers] = this.parseTurnNumbers(
        properties.content
      );
      properties.content = state as number;
      properties.moveNumbers = numbers as string[];
      properties.locked = locked as boolean;
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
