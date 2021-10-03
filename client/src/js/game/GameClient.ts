import ISubscriber from "../common/interfaces/ISubscriber";
import { UniqueId } from "../common/UniqueId";
import IServer from "../server/IServer";
import { Dimensions, GameResponse } from "../types";
import Controller from "./controllers/Controller";
import TicTacToe from "./TicTacToe";

export default class GameClient implements ISubscriber {
  private gameId: string;
  private gameStatus: GameResponse;
  private playerId: string;
  private canvas: HTMLCanvasElement;
  private server: IServer;
  private resizeController: Controller;
  private gameController: Controller;
  private subscriberId: UniqueId;
  private game: TicTacToe;
  private connected: boolean;

  constructor(
    server: IServer,
    game: TicTacToe,
    gameController: Controller,
    resizeController: Controller,
    canvas: HTMLCanvasElement
  ) {
    this.gameId = "";
    this.gameStatus = "initial";
    this.playerId = "";
    this.server = server;
    this.canvas = canvas;
    this.game = game;
    this.gameController = gameController;
    this.resizeController = resizeController;
    this.subscriberId = new UniqueId();
    this.connected = false;
    this.subscribe();
  }

  private subscribe(): void {
    this.server.add(this);
    this.gameController.add(this);
    this.resizeController.add(this);
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
    this.resizeController.connect();
    this.gameController.connect();
    this.game.draw();
    this.server.start();
  }

  public update(): void {
    if (this.gameController.hasCoordinates(this)) {
      this.makeMove();
    } else if (this.resizeController.hasDimensions()) {
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

  private getGameStatus() {
    return this.gameStatus;
  }

  private isConnected() {
    return this.connected;
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

  private handleMessage(): void {
    const msg = this.server.getMessageIn();
    this.gameStatus = msg.status;
    this.gameId = msg.gameId;
    this.playerId = msg.playerId;
    switch (this.gameStatus) {
      case "inLobby":
        break;
      case "inGame":
        this.updateState(msg.state);
        this.game.setWinner(msg.winner);
        break;
      case "gameOver":
        this.updateState(msg.state);
        this.game.setWinner(msg.winner);
        break;
      case "initial":
        break;
      case "playerRejoin":
        break;
      case "playerLeft":
        break;
      default:
        console.log(
          `Invalid msg.status ${msg.status} message in handle message`
        );
    }
  }

  private updateState(state: number): void {
    this.game.setState(state);
  }

  private getElement(): HTMLElement {
    return this.canvas;
  }

  private resizeGame(): void {
    const dimensions = this.resizeController.getDimensions();
    this.canvas.width = dimensions[0];
    this.canvas.height = dimensions[1];
    this.game.setDimensions(dimensions);
    this.game.draw();
  }

  private makeMove(): void {
    const coordinates = this.gameController.getCoordinates(this);
    const quadrant = this.game.getQuadrantNumber(coordinates);
    console.log("quadrant: " + quadrant);
    if (!this.connected) {
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
