import React, {
  useCallback,
  useEffect,
  useState,
  useRef,
  ReactElement,
} from "react";
import Arcade from "./Arcade";
import Menu from "./Menu";

export default function App(): ReactElement {
  const [inGame, setInGame] = useState(false);

  const handleStartButton = () => {
    setInGame(!inGame);
  };

  return (
    <div className="main">
      {inGame ? (
        <Arcade></Arcade>
      ) : (
        <Menu handleStartButton={handleStartButton}></Menu>
      )}
    </div>
  );
}
