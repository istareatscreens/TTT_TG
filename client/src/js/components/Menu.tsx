import React, {
  useCallback,
  useEffect,
  useState,
  useRef,
  ReactElement,
} from "react";

interface Props {
  handleStartButton: () => void;
}

export default function Menu({ handleStartButton }: Props): ReactElement {
  return (
    <>
      <div className="menu">
        <div className="mdc-card">
          <h1 className="menu__title title">Tic Tac Toe</h1>
          <div className="menu__buttons">
            <button
              onClick={handleStartButton}
              className="btn mdc-button mdc-button--raised"
            >
              <span className="mdc-button__label">Start Game</span>
            </button>
          </div>
        </div>
      </div>
    </>
  );
}
