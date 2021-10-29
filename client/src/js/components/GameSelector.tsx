import React, { ReactElement, useRef, useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { GameName } from "../types/game";

interface GameSelectorProps {
  gamesInfo: GameInfo[];
  setGame: (gameName: GameName) => void;
}

export interface GameInfo {
  id: number;
  name: string;
  gameName: GameName;
  image: any;
  info: string[];
  link: string;
}

const GameSelector = ({
  gamesInfo,
  setGame,
}: GameSelectorProps): ReactElement => {
  const [displayInfo, setDisplayInfo] = useState<boolean>(false);
  const [info, setInfo] = useState<string[]>([]);
  const [link, setLink] = useState<string>("");

  const handleInfoClick = (info: string[], link: string) => {
    return (
      <div className="menu--info-padding">
        <p className="menu__selector__info">
          {info.map((sentence) => (
            <>
              {sentence}
              <br />
              <br />
            </>
          ))}
        </p>
        <div className="menu__selector__btn-container">
          <button className="btn btn-info--shrink btn-info mdc-button mdc-button--raised">
            <span
              onClick={() => setDisplayInfo(false)}
              className="mdc-button__label"
            >
              Back
            </span>
          </button>
          <button className="btn btn-info btn-info--shrink mdc-button mdc-button--raised">
            <span
              onClick={() => window.open(link)}
              className="mdc-button__label"
            >
              More Info
            </span>
          </button>
        </div>
      </div>
    );
  };

  const createGameMenu = (gamesInfo: GameInfo) => {
    const { name, image, info, link, gameName } = gamesInfo;
    return (
      <div className="menu__selector">
        <img className="menu__selector__image" src={image}></img>
        <div className="menu__selector__btn-container">
          <button
            onClick={() => setGame(gameName)}
            className="btn mdc-button mdc-button--raised"
          >
            <span className="mdc-button__label">{name}</span>
          </button>
          <button className="btn btn-info mdc-button mdc-button--raised">
            <span
              onClick={() => {
                setInfo(info);
                setLink(link);
                setDisplayInfo(true);
              }}
              className="mdc-button__label"
            >
              ?
            </span>
          </button>
        </div>
      </div>
    );
  };

  return (
    <>
      <div className="menu menu__selector">
        <div className="mdc-card">
          <h1 className="menu__title title menu--selector-padding">
            {displayInfo ? "Info" : "Select game"}
          </h1>
          <div className="menu__buttons--selector menu_buttons menu--selector-padding">
            {displayInfo
              ? handleInfoClick(info, link)
              : gamesInfo.map((gameInfo: GameInfo) => createGameMenu(gameInfo))}
          </div>
        </div>
      </div>
    </>
  );
};

export default GameSelector;
