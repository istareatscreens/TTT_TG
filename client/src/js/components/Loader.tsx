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
        <div className="loader__message loader__message--adjustment fade-in">
          <div className="dots-wrapper">
            {message}
            <p>·</p>
            <p>·</p>
            <p>·</p>
          </div>
        </div>
      </div>
    </>
  );
}
