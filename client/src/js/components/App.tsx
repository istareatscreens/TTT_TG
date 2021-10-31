import React, { ReactElement } from "react";
import Arcade from "./Arcade";
import Menu from "./Menu";
import { Switch, Route, Redirect } from "react-router-dom";

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
        <Route path="/arcade" exact>
          <Arcade />
        </Route>
        <Route path="/QTicTacToe" exact>
          <Arcade />
        </Route>
        <Route path="/TicTacToe" exact>
          <Arcade />
        </Route>
        <Route path="/QTicTacToe/:id" exact>
          <Arcade />
        </Route>
        <Route path="/TicTacToe/:id" exact>
          <Arcade />
        </Route>
        <Route render={() => <Redirect to={{ pathname: "/" }} />} />
      </Switch>
    </div>
  );
}
