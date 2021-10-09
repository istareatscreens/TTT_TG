import React, { ReactElement, useRef, useState, useEffect } from "react";

interface Props {}

export default function Loader(): ReactElement {
  return (
    <>
      <div className="loader-backdrop" />
      <div className="loader">
        <div className="board game-loader">
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
      </div>
    </>
  );
}
