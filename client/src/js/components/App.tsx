import React, { ReactElement } from "react";
import Arcade from "./Arcade";
import Menu from "./Menu";
import { Switch, Route } from "react-router-dom";

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
        <Route path="/:id" exact>
          <Arcade />
        </Route>
      </Switch>
    </div>
  );
}
