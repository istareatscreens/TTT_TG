import IGame from "./game/IGame";
import QTicTacToe from "./game/QTicTacToe";
import TicTacToe from "./game/TicTacToe";
import { Dimensions, GameName } from "./types/game";
import ttt from "../images/ttt.png";
import qttt from "../images/qttt.png";

export interface GameInfo {
  id: number;
  alias: string;
  gameName: GameName;
  fullName: string;
  image: any;
  imageAlt: string;
  info: string[];
  link: string;
  createGame: (
    context: CanvasRenderingContext2D,
    dimension: Dimensions
  ) => IGame;
}

export const gamesInfo: GameInfo[] = [
  {
    id: 0,
    alias: "Classic",
    gameName: "TicTacToe",
    fullName: "Tic Tac Toe",
    image: ttt,
    imageAlt: `Tic Tac Toe image`,
    info: [
      "Each player alternates between placing a mark on the board.",
      "The first player to place a horizontal, veritcle, or diagonal line of marks wins",
    ],
    link: "https://en.wikipedia.org/wiki/Tic-tac-toe",
    createGame: (
      context: CanvasRenderingContext2D,
      dimensions: Dimensions
    ): IGame => new TicTacToe(context, dimensions),
  },
  {
    id: 1,
    alias: "Quantum",
    gameName: "QTicTacToe",
    fullName: "Quantum Tic Tac Toe",
    image: qttt,
    imageAlt: `Quantum Tic Tac Toe image`,
    info: [
      "Each player places two spooky marks in two different outer quadrants.",
      "Each set of moves creates a link between outer quadrants.",
      "On formation of a cycle on the board through linked quadrants the system will collapse to classical marks (two states possible).",
      "First player to form a line of classical marks wins",
    ],
    link: "https://en.wikipedia.org/wiki/Quantum_tic-tac-toe",
    createGame: (
      context: CanvasRenderingContext2D,
      dimensions: Dimensions
    ): IGame => new QTicTacToe(context, dimensions),
  },
];
