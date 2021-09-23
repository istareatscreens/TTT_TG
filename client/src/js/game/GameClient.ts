import ISubscriber from "../common/interfaces/ISubscriber";
import { UniqueId } from "../common/UniqueId";
import Server from "../server/Server";
import { Dimensions, GameStatus } from "../types";
import Controller from "./controllers/Controller";
import TicTacToe from "./TicTacToe";

export default class GameClient implements ISubscriber {
  private gameId: number;
  private gameStatus: GameStatus;
  private playerID: number;
  private canvas: HTMLCanvasElement;
  private server: Server;
  private resizeController: Controller;
  private gameController: Controller;
  private subscriberId: UniqueId;
  private game: TicTacToe;
  private connected: boolean;

  constructor(
    server: Server,
    game: TicTacToe,
    gameController: Controller,
    resizeController: Controller,
    canvas: HTMLCanvasElement
  ) {
    this.gameId = -1;
    this.gameStatus = "initial";
    this.playerID = -1;
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
      this.handleConnection();
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

  private handleConnection(): void {
    this.connected = true;
    this.server.send({
      type: "joinGame",
      gameId: -1,
      quadrant: -1,
    });
  }

  private handleDisconnection(): void {
    this.connected = false;
  }

  private handleMessage(): void {
    const msg = this.server.getMessageIn();
    this.gameStatus = msg.status;
    this.gameId = msg.gameId;
    switch (this.gameStatus) {
      case "inLobby":
        break;
      case "inGame":
        this.updateState(msg.state);
        this.game.setWinner(msg.winner);
        break;
      case "Finished":
        this.updateState(msg.state);
        this.game.setWinner(msg.winner);
        break;
      case "initial":
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
      gameId: this.gameId,
      quadrant: quadrant,
    });
  }
}
