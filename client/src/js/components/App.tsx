import React, { useState, ReactElement } from "react";
import Arcade from "./Arcade";
import Menu from "./Menu";
import { RouteComponentProps, Switch, Route } from "react-router-dom";

interface Props {
  alt: string;
}

export default function App(): ReactElement {
  return (
    <div className="main">
      <Switch>
        <Route path="/" exact>
          <Menu />
        </Route>
        <Route path="/lobby" exact>
          <Arcade />
        </Route>
        <Route path="/:id" exact>
          <Arcade />
        </Route>
      </Switch>
      {/*
      {inGame ? (
        <Arcade></Arcade>
      ) : (
        <Route
          path="/"
          render={(_: RouteComponentProps) => (
            <Menu handleStartButton={handleStartButton}></Menu>
          )}
        />
      )}
      */}
    </div>
  );
}
