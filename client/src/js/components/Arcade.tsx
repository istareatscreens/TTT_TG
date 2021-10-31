import React, { ReactElement, useRef, useState, useEffect } from "react";
import { validate as validateUuid } from "uuid";
import { useHistory, useLocation, useParams } from "react-router-dom";
import { Location } from "history";

import Game from "./Game";
import GameSelector from "./selector/GameSelector";
import { Dimensions, GameName } from "../types/game";
import IGame from "../game/IGame";
import QTicTacToe from "../game/QTicTacToe";
import TicTacToe from "../game/TicTacToe";
import { gamesInfo, GameInfo } from "../GameData";

interface Props {}

export default function Arcade({}: Props): ReactElement {
  const [gameSelected, setGameSelected] = useState<GameInfo | "">("");
  const [gameId, setGameId] = useState<string>("");
  const history = useHistory();
  const location = useLocation();
  const { id } = useParams<{ id: string }>();

  useEffect(() => {
    handleLoad();
  }, [location]);

  const getGameFromUrl = () => {
    const gameSelected = getGameInfoFromUrl();
    setGameSelected(gameSelected ? gameSelected : "");
  };

  const parseUrlArguments = () => {
    return location.pathname.split("/");
  };

  const getGameInfoFromUrl = (): GameInfo | "" => {
    const urlGameName = parseUrlArguments();
    return gamesInfo.find(
      (gameInfo) => gameInfo.gameName === urlGameName?.[1] ?? ""
    );
  };

  const getGameIdFromUrl = (): void => {
    const gameId: string = parseUrlArguments()?.[2] ?? "";
    setGameId(validateUuid(gameId) ? gameId : "");
  };

  const updateGameId = (gameId: string) => {
    // url hack
    if (!gameSelected) {
      return;
    }

    const { gameName, fullName } = gameSelected;
    window.history.replaceState(null, `${fullName}`, `/${gameName}/${gameId}`);
    history.push(`/${gameName}/${gameId}`);
  };

  const handleInvalidGame = () => {
    history.push("/arcade");
  };

  const handleLoad = () => {
    getGameIdFromUrl();
    getGameFromUrl();
  };

  return (
    <div className="games-container">
      {gameSelected ? (
        <Game
          updateGameId={updateGameId}
          handleInvalidGame={handleInvalidGame}
          gameId={gameId}
          gameInfo={gameSelected}
          setGameSelected={() => {
            setGameSelected("");
          }}
        />
      ) : (
        <GameSelector gamesInfo={gamesInfo} />
      )}
    </div>
  );
}
