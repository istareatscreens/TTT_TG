import React from "react";
import ReactDOM from "react-dom";
import App from "./components/App";
import { BrowserRouter, HashRouter } from "react-router-dom";

function hideLoader() {
  const elements = document.getElementById("loader");
  console.log(elements);
  hideAllElements(elements);
}

function hideAllElements(elements: any) {
  elements = elements.length ? elements : [elements];
  for (var index = 0; index < elements.length; index++) {
    elements[index].style.display = "none";
  }
}

ReactDOM.render(
  <React.StrictMode>
    <HashRouter>
      <App />
    </HashRouter>
  </React.StrictMode>,
  document.getElementById("root"),
  hideLoader
);
