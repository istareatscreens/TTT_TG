import React, { Fragment, useState } from "react";
import { GameInfo } from "../../GameData";

interface GameInfoProps {
  gameInfo: GameInfo;
  setGameInfo: (gameInfo: GameInfo) => void;
}

const InfoMenu = ({ gameInfo, setGameInfo }: GameInfoProps) => {
  const [click, setClick] = useState<number>(0);

  return (
    <div className="menu--info-padding fade-in">
      <p className="menu__selector__info">
        {gameInfo.info.map((sentence, index) => (
          <Fragment key={index}>
            {sentence}
            <br />
            <br />
          </Fragment>
        ))}
      </p>
      <div className="menu__selector__btn-container">
        <button
          onClick={() => {
            setGameInfo(null);
          }}
          className="btn btn-info--shrink btn-info mdc-button mdc-button--raised"
        >
          <span className="mdc-button__label">Back</span>
        </button>
        <button
          onClick={() => window.open(gameInfo.link)}
          className="btn btn-info--shrink btn-info mdc-button mdc-button--raised"
        >
          <span className="mdc-button__label">Wiki</span>
        </button>
      </div>
    </div>
  );
};

export default InfoMenu;
