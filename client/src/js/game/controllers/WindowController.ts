import ISubscriber from "../../common/interfaces/ISubscriber";
import { Dimensions } from "../../types/game";
import Controller from "./Controller";

export default class WindowController implements Controller {
  private dimensions: Dimensions[];
  private element: HTMLDivElement;
  private subscribers: Map<string, ISubscriber>;

  public constructor(element: HTMLDivElement) {
    this.element = element;
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
    window.removeEventListener("resize", this.setDimensions.bind(this));
    window.addEventListener("resize", this.setDimensions.bind(this));
  }

  public hasDimensions(): boolean {
    return this.dimensions.length !== 0;
  }

  private setDimensions(e: UIEvent) {
    const dimension = Math.min(
      this.element.clientWidth,
      this.element.clientHeight
    );
    this.dimensions.push([dimension, dimension]);
    this.notify();
  }

  public getDimensions(): Dimensions {
    return this.dimensions.shift();
  }
}
