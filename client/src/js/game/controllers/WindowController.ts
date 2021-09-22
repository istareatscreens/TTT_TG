import ISubscriber from "../../common/interfaces/ISubscriber";
import { Dimensions } from "../../types";
import Controller from "./Controller";

export default class WindowController implements Controller {
  private dimensions: Dimensions[];
  private window: Window;
  private subscribers: Map<string, ISubscriber>;

  public constructor(window: Window) {
    this.window = window;
    this.dimensions = [];
    this.subscribers = new Map<string, ISubscriber>();
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

  public connect(): void {
    this.window.removeEventListener("resize", this.setDimensions.bind(this));
    this.window.addEventListener("resize", this.setDimensions.bind(this));
  }

  public hasDimensions(): boolean {
    return this.dimensions.length !== 0;
  }

  private setDimensions(e: UIEvent) {
    this.dimensions.push([this.window.innerWidth, this.window.innerHeight]);
    this.notify();
  }

  public getDimensions(): Dimensions {
    return this.dimensions.shift();
  }
}
