import React, { ReactElement } from "react";
import { Link } from "react-router-dom";
import { GameInfo } from "../../GameData";
import SuspenseImage from "../library/SuspenseImage";

interface GameMenuProps {
  gameInfo: GameInfo;
  handleInfoClick: (gameInfo: GameInfo) => void;
}

const GameMenu = ({
  gameInfo,
  handleInfoClick,
}: GameMenuProps): ReactElement => {
  const { name, image, id, imageAlt, gameName } = gameInfo;
  return (
    <div key={id} className="menu__selector">
      <SuspenseImage
        key={id}
        className="menu__selector__image"
        src={image}
        alt={imageAlt}
      ></SuspenseImage>
      <div className="menu__selector__btn-container">
        <Link to={`/${gameName}`}>
          <button className="btn mdc-button mdc-button--raised">
            <span className="mdc-button__label">{name}</span>
          </button>
        </Link>
        <button
          onClick={() => handleInfoClick(gameInfo)}
          className="btn btn-info mdc-button mdc-button--raised"
        >
          <span className="mdc-button__label">?</span>
        </button>
      </div>
    </div>
  );
};

export default GameMenu;
