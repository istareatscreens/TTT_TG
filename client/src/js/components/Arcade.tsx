import React, { ReactElement, useRef, useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import MouseController from "../game/controllers/MouseController";
import IServer from "../server/IServer";
import SocketServer from "../server/SocketServer";
import Game from "./Game";

interface Props {}

export default function Arcade({}: Props): ReactElement {
  useEffect(() => {}, []);

  const { id } = useParams<{ id: string }>();

  return (
    <div className="games-container">
      <Game />
    </div>
  );
}
