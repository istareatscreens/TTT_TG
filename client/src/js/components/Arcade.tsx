import React, { ReactElement, useRef, useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import Game from "./Game";
import GameSelector, { GameInfo } from "./GameSelector";
import { GameName } from "../types/game";
import ttt from "../../images/ttt.png";
import qttt from "../../images/qttt.png";

interface Props {}

export default function Arcade({}: Props): ReactElement {
  const [gameSelected, setGameSelected] = useState<GameName | "">("");
  const { id } = useParams<{ id: string }>();
  const [gamesInfo, setGamesInfo] = useState<GameInfo[]>([
    {
      id: 0,
      name: "Classic",
      gameName: "TicTacToe",
      image: ttt,
      info: [
        "Each player alternates between placing a mark on the board.",
        "The first player to place a horizontal, veritcle, or diagonal line of marks wins",
      ],
      link: "https://en.wikipedia.org/wiki/Tic-tac-toe",
    },
    {
      id: 1,
      name: "Quantum",
      gameName: "QTicTacToe",
      image: qttt,
      info: [
        "Each player places two spooky marks in two different outer quadrants.",
        "Each set of moves creates a link between outer quadrants.",
        "On formation of a cycle on the board through linked quadrants the system will collapse to classical marks (two states possible).",
        "First player to form a line of classical marks wins",
      ],
      link: "https://en.wikipedia.org/wiki/Quantum_tic-tac-toe",
    },
  ]);

  useEffect(() => {}, []);

  return (
    <div className="games-container">
      {gameSelected ? (
        <Game
          setGameSelected={() => {
            setGameSelected("");
          }}
          gameSelected={gameSelected}
        />
      ) : (
        <GameSelector setGame={setGameSelected} gamesInfo={gamesInfo} />
      )}
    </div>
  );
}
