import ISubscriber from "../../common/interfaces/ISubscriber";
import { Coordinates } from "../../types";
import Controller from "./Controller";

export default class MouseController implements Controller {
  private coordinates: Coordinates[];
  private canvas: HTMLCanvasElement;
  private subscribers: Map<string, ISubscriber>;

  public constructor(canvas: HTMLCanvasElement) {
    this.canvas = canvas;
    this.coordinates = [];
    this.subscribers = new Map<string, ISubscriber>();
  }

  public add(subscriber: ISubscriber): void {
    this.subscribers = this.subscribers.set(
      subscriber.getSubscriberId(),
      subscriber
    );
  }

  public remove(subscriber: ISubscriber): void {
    this.subscribers.delete(subscriber.getSubscriberId());
  }

  public notify(): void {
    this.subscribers.forEach((subscriber) => subscriber.update());
  }

  public connect(): void {
    this.canvas.removeEventListener("click", this.setCoordinates.bind(this));
    this.canvas.addEventListener("click", this.setCoordinates.bind(this));
  }

  public hasCoordinates(): boolean {
    return this.coordinates.length !== 0;
  }

  private setCoordinates(e: MouseEvent) {
    this.coordinates.push([e.offsetX, e.offsetY]);
    this.notify();
  }

  public getCoordinates(): Coordinates {
    return this.coordinates.shift();
  }
}
