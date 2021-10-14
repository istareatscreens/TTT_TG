import React, { ReactElement, useRef, useState, useEffect } from "react";

interface Props {
  message: string;
}

export default function Loader({ message }: Props): ReactElement {
  return (
    <>
      <div className="loader-backdrop fade-in" />
      <div className="loader">
        <div className="board game-loader fade-in">
          <div>
            <p>O</p>
          </div>
          <div>
            <p>X</p>
          </div>
          <div>
            <p>X</p>
          </div>
          <div>
            <p>O</p>
          </div>
          <div>
            <p>X</p>
          </div>
          <div>
            <p>O</p>
          </div>
          <div>
            <p>X</p>
          </div>
          <div>
            <p>O</p>
          </div>
          <div>
            <p>O</p>
          </div>
        </div>
        <div className="loader__message fade-in">
          {message}
          <div className="dots-wrapper">
            <p>·</p>
            <p>·</p>
            <p>·</p>
          </div>
        </div>
      </div>
    </>
  );
}
