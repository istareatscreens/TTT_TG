import React, { ReactElement, useRef, useState, useEffect } from "react";
import MouseController from "../game/controllers/MouseController";
import IServer from "../server/IServer";
import SocketServer from "../server/SocketServer";
import Game from "./Game";

interface Props {}

export default function Arcade({}: Props): ReactElement {
  useEffect(() => {}, []);

  return (
    <div className="games-container">
      <Game></Game>
    </div>
  );
}
