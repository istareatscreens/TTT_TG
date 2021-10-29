import ISubscriber from "../common/interfaces/ISubscriber";
import { TTTMessageIn, TTTMessageOut } from "../game/message/TicTacToeMessage";
import IServer from "./IServer";
const url = "ws://localhost:80/ws/";

export default class SocketServer implements IServer {
  private socket: WebSocket;
  private subscribers: Map<string, ISubscriber>;
  private messagesIn: TTTMessageIn[];
  private connected: boolean;

  constructor() {
    this.subscribers = new Map<string, ISubscriber>();
    this.connected = false;
    this.messagesIn = [];
  }

  public getMessageIn(): TTTMessageIn {
    return this.messagesIn.shift();
  }

  public hasMessageIn(): boolean {
    return this.messagesIn.length !== 0;
  }

  public add(subscriber: ISubscriber): void {
    this.subscribers.set(subscriber.getSubscriberId(), subscriber);
  }

  public remove(subscriber: ISubscriber): void {
    this.subscribers.delete(subscriber.getSubscriberId());
  }

  public notify(): void {
    this.subscribers.forEach((subscriber) => subscriber.update());
  }

  private socketOnMessage(msg: MessageEvent): void {
    this.setMessage(JSON.parse(msg.data) as TTTMessageIn);
    this.notify();
  }

  private setMessage(msg: TTTMessageIn) {
    this.messagesIn.push(msg);
  }

  private socketOnOpen(msg: MessageEvent): void {
    console.log("websocket opened");
    this.connected = true;
    this.notify();
  }

  private socketOnClose(): void {
    console.log("websocket disconnected - waiting for connection");
    this.connected = false;
    this.notify();
  }

  public isConnected() {
    return this.connected;
  }

  public send(message: TTTMessageOut): void {
    this.socket.send(JSON.stringify(message));
  }

  private socketOnError(event: Error): void {
    console.log("erorr");
  }

  public start(): void {
    this.socket = new WebSocket(url);
    setTimeout(() => {
      if (this.socket.readyState !== 1) {
        this.socket = new WebSocket(url);
      }
    }, 3000);
    this.socket.onerror = this.socketOnError.bind(this);
    this.socket.onopen = this.socketOnOpen.bind(this);
    this.socket.onclose = this.socketOnClose.bind(this);
    this.socket.onmessage = this.socketOnMessage.bind(this);
  }
}
