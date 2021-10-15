import { Mark } from "../../../common/enums";
import GameBoard from "../GameBoard";
import GameBoared from "../GameBoard";

interface IState {
  setState: (state: number | number[]) => void;
  IterateOverState: (
    callback: (x: number, y: number, content: Mark | number) => void
  ) => void;
}
