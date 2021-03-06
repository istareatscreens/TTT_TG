import React, {
  useCallback,
  useEffect,
  useState,
  useRef,
  ReactElement,
} from "react";
import { Link } from "react-router-dom";

interface Props {}

export default function Menu(): ReactElement {
  return (
    <>
      <div className="menu fade-in">
        <div className="mdc-card">
          <h1 className="menu__title title menu--title-padding">
            TTT Turn-based
            <br />
            Games
          </h1>
          <div className="menu__buttons">
            <Link to="/arcade">
              <button className="btn mdc-button mdc-button--raised">
                <span className="mdc-button__label">Play Game</span>
              </button>
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
