import React, { ReactElement, useState, Suspense, useEffect } from "react";
import { GameInfo } from "../../GameData";
import Loader from "../Loader";
import GameMenu from "./GameMenu";
import InfoMenu from "./InfoMenu";

interface GameSelectorProps {
  gamesInfo: GameInfo[];
}

const GameSelector = ({ gamesInfo }: GameSelectorProps): ReactElement => {
  const [selectedInfo, setSelectedInfo] = useState<GameInfo | null>(null);

  return (
    <>
      <Suspense fallback={<Loader message="Loading"></Loader>}>
        <div className="menu menu__selector fade-in">
          <div className="mdc-card">
            <h1 className="menu__title title menu--selector-padding">
              {selectedInfo ? (
                <>
                  {selectedInfo.fullName}
                  <br />
                  Info
                </>
              ) : (
                "Select game"
              )}
            </h1>
            <div className="menu__buttons--selector menu_buttons menu--selector-padding">
              {selectedInfo ? (
                <InfoMenu
                  key={0}
                  gameInfo={selectedInfo}
                  setGameInfo={setSelectedInfo}
                />
              ) : (
                gamesInfo.map((gameInfo: GameInfo, index) => (
                  <GameMenu
                    key={index}
                    gameInfo={gameInfo}
                    handleInfoClick={setSelectedInfo}
                  />
                ))
              )}
            </div>
          </div>
        </div>
      </Suspense>
    </>
  );
};

export default GameSelector;
