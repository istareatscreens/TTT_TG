import { Mark } from "../common/enums";
import ISubscriber from "../common/interfaces/ISubscriber";
import { UniqueId } from "../common/UniqueId";
import IServer from "../server/IServer";
import { Dimensions, GameResponse } from "../types";
import Controller from "./controllers/Controller";
import { TTTMessageIn } from "./message/TicTacToeMessage";
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
}

export default class GameClient implements ISubscriber {
  private gameId: string;
  private gameStatus: GameResponse;
  private playerId: string;
  private canvas: HTMLCanvasElement;
  private server: IServer;
  private controllers: Controllers;
  private subscriberId: UniqueId;
  private game: TicTacToe;
  private connected: boolean;
  private frontEndCallbacks: FrontEndCallbacks;

  constructor(
    server: IServer,
    game: TicTacToe,
    controllers: Controllers,
    canvas: HTMLCanvasElement,
    frontEndCallBacks: FrontEndCallbacks
  ) {
    this.server = server;
    this.canvas = canvas;
    this.game = game;
    this.setInitialVariables();
    this.controllers = controllers;
    this.frontEndCallbacks = frontEndCallBacks;
    this.subscriberId = new UniqueId();
    this.connected = false;
    this.subscribe();
  }

  private setInitialVariables() {
    this.gameId = "";
    this.gameStatus = "initial";
    this.playerId = "";
  }

  private reset() {
    this.setInitialVariables();
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
    } else if (this.server.isConnected()) {
      this.joinLobby();
    } else if (this.server.isConnected()) {
      this.reconnectToGame();
    } else if (!this.server.isConnected()) {
      this.handleDisconnection();
    }
  }

  private joinLobby(): void {
    this.connected = true;
    this.server.send({
      type: "joinLobby",
      game: "TicTacToe",
      gameId: this.gameId,
      playerId: this.playerId,
      position: null,
    });
  }

  private reconnectToGame(): void {
    this.connected = true;
    this.server.send({
      type: "joinGame",
      game: "TicTacToe",
      gameId: this.gameId,
      playerId: this.playerId,
      position: null,
    });
  }

  private handleDisconnection(): void {
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

  private handleMessage(): void {
    const message = this.server.getMessageIn();
    const gameOverState = message.gameOverState;
    this.playerId = message.playerId;
    this.gameId = message.gameId;
    this.gameStatus = message.status;
    this.game.setMark(message.playerNumber);

    switch (this.gameStatus) {
      case "inLobby":
        break;
      case "inGame":
        this.updateState(message.state);
        this.game.setWinner(message.winner);
        break;
      case "gameOver":
        this.updateState(message.state);
        this.game.setWinner(message.winner);
        this.resetGameId();
        this.frontEndCallbacks.setGameOver(true);
        if (gameOverState) {
          this.game.setGameOverState(gameOverState);
          this.game.draw();
        }
        break;
      case "initial":
        this.frontEndCallbacks.setGameOver(false);
        break;
      case "playerRejoin":
        break;
      case "playerLeft":
        break;
      default:
        console.log(
          `Invalid msg.status ${message.status} message in handle message`
        );
    }
    this.updateFrontEnd(message);
  }

  private updateState(state: number): void {
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
    console.log("quadrant: " + quadrant);
    if (!this.connected || quadrant == null || !this.game.isTurn()) {
      return;
    }
    this.server.send({
      type: "makeMove",
      game: "TicTacToe",
      gameId: this.gameId,
      playerId: this.playerId,
      position: quadrant,
    });
  }
}
