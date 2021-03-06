import ISubscriber from "../common/interfaces/ISubscriber";
import { UniqueId } from "../common/UniqueId";
import IServer from "../server/IServer";
import { Dimensions, GameResponse } from "../types/game";
import { States } from "./bored/state/IGameState";
import Controller from "./controllers/Controller";
import IGame from "./IGame";
import { TTTMessageIn } from "./message/Message";
import TicTacToe from "./TicTacToe";

interface Controllers {
  resizeController: Controller;
  gameController: Controller;
}

interface FrontEndCallbacks {
  setWinner?: (input: any) => void;
  setTurn?: (input: any) => void;
  setPlayerNumber?: (input: any) => void;
  setGameStatus?: (input: any) => void;
  setGameOver?: (input: any) => void;
  setPlayerDisconnected?: (input: any) => void;
  setInLobby?: (input: any) => void;
  setConnected?: (input: any) => void;
  handleInvalidGame?: () => void;
  updateGameId?: (gameId: string) => void;
}

interface GameClientProperties {
  gameId: string;
  canvas: HTMLCanvasElement;
}

export default class GameClient implements ISubscriber {
  private gameId: string;
  private gameStatus: GameResponse;
  private canvas: HTMLCanvasElement;
  private server: IServer;
  private controllers: Controllers;
  private subscriberId: UniqueId;
  private game: IGame;
  private connected: boolean;
  private frontEndCallbacks: FrontEndCallbacks;

  constructor(
    server: IServer,
    game: IGame,
    controllers: Controllers,
    frontEndCallBacks: FrontEndCallbacks,
    properties: GameClientProperties
  ) {
    this.server = server;
    this.canvas = properties.canvas;
    this.game = game;
    this.controllers = controllers;
    this.frontEndCallbacks = frontEndCallBacks;
    this.subscriberId = new UniqueId();
    this.connected = false;
    this.setInitialVariables(properties.gameId);
    this.subscribe();
  }

  private setInitialVariables(gameId: string = "") {
    this.gameId = gameId;
    this.gameStatus = "initial";
  }

  public reset() {
    this.setInitialVariables();
    this.frontEndCallbacks.setGameOver(false);
    this.game.reset();
    this.game.draw();
    this.joinLobby();
  }

  private subscribe(): void {
    this.server.add(this);
    this.controllers.gameController.add(this);
    this.controllers.resizeController.add(this);
  }

  public getSubscriberId(): string {
    return this.subscriberId.getId();
  }

  public getSubscriberElement(): HTMLElement {
    return this.canvas;
  }

  public redraw(dimensions: Dimensions) {
    this.game.setDimensions(dimensions);
    this.game.draw();
  }

  public start() {
    this.controllers.resizeController.connect();
    this.controllers.gameController.connect();
    this.game.draw();
    this.server.start();
  }

  public update(): void {
    if (this.controllers.gameController.hasCoordinates(this)) {
      this.makeMove();
    } else if (this.controllers.resizeController.hasDimensions()) {
      this.resizeGame();
    } else if (this.server.hasMessageIn()) {
      this.handleMessage();
    } else if (this.server.isConnected() && !this.gameId) {
      this.joinLobby();
    } else if (this.server.isConnected()) {
      this.reconnectToGame();
    } else if (!this.server.isConnected()) {
      this.handleDisconnection();
    }
  }

  private joinLobby(): void {
    this.connected = true;
    this.frontEndCallbacks.setConnected(true);
    this.server.send({
      type: "joinLobby",
      game: this.game.getName(),
      gameId: this.gameId,
      position: null,
    });
  }

  private reconnectToGame(): void {
    this.connected = true;
    this.frontEndCallbacks.setConnected(true);
    this.server.send({
      type: "joinGame",
      game: this.game.getName(),
      gameId: this.gameId,
      position: null,
    });
  }

  private handleDisconnection(): void {
    this.frontEndCallbacks.setConnected(false);
    this.connected = false;
  }

  private updateFrontEnd(msg: TTTMessageIn): void {
    this.frontEndCallbacks.setWinner(msg.winner);
    this.frontEndCallbacks.setPlayerNumber(msg.playerNumber);
    this.frontEndCallbacks.setTurn(this.game.getTurn());
    this.frontEndCallbacks.setGameStatus(this.gameStatus);
  }

  private resetGameId(): void {
    this.gameId = "";
  }

  private setGameId(gameId: string) {
    if (this.gameId === "" && gameId !== "" && gameId !== this.gameId) {
      this.gameId = gameId;
      this.frontEndCallbacks.updateGameId(this.gameId);
    }
  }

  private handleGameMessage(message: TTTMessageIn): void {
    this.setGameId(message.gameId);
    this.game.setMark(message.playerNumber);
    this.game.setTurn(message.turn);
  }

  private handleMessage(): void {
    const message = this.server.getMessageIn();
    const gameOverState = message?.gameOverState ?? false;
    this.gameStatus = message.status;

    switch (this.gameStatus) {
      case "inLobby":
        this.frontEndCallbacks.setInLobby(true);
        break;
      case "inGame":
        this.handleGameMessage(message);
        this.frontEndCallbacks.setInLobby(false);
        this.updateState(message.state);
        this.game.setWinner(message.winner);
        break;
      case "gameOver":
        this.handleGameMessage(message);
        this.updateState(message.state);
        this.game.setWinner(message.winner);
        this.resetGameId();
        this.frontEndCallbacks.setGameOver(true);
        if (gameOverState) {
          this.game.setGameOverState(gameOverState);
        }
        break;
      case "playerRejoin":
        this.handleGameMessage(message);
        this.frontEndCallbacks.setPlayerDisconnected(false);
        break;
      case "playerLeft":
        this.handleGameMessage(message);
        this.frontEndCallbacks.setPlayerDisconnected(true);
        break;
      case "invalidGame":
        this.frontEndCallbacks.handleInvalidGame();
        break;
      default:
        console.log(
          `Invalid msg.status ${message.status} message in handle message`
        );
        return;
    }
    this.updateFrontEnd(message);
  }

  private updateState(state: States): void {
    this.game.setState(state);
  }

  private getElement(): HTMLElement {
    return this.canvas;
  }

  private resizeGame(): void {
    const dimensions = this.controllers.resizeController.getDimensions();
    this.canvas.width = dimensions[0];
    this.canvas.height = dimensions[1];
    this.game.setDimensions(dimensions);
    this.game.draw();
  }

  private makeMove(): void {
    const coordinates = this.controllers.gameController.getCoordinates(this);
    const quadrant = this.game.getQuadrantNumber(coordinates);
    if (
      !this.connected ||
      quadrant == null ||
      !this.game.isTurn() ||
      this.game.gameIsOver()
    ) {
      return;
    }
    this.server.send({
      type: "makeMove",
      game: this.game.getName(),
      gameId: this.gameId,
      position: quadrant,
    });
  }
}
