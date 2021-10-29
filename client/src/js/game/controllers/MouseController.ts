import ISubscriber from "../../common/interfaces/ISubscriber";
import { Coordinates } from "../../types/game";
import Controller from "./Controller";

type ElementData = [element: HTMLElement, isAttached: boolean];

export default class MouseController implements Controller {
  private coordinates: Map<string, Coordinates[]>;
  private subscribers: Map<string, ISubscriber>;
  private elements: Map<string, ElementData>;

  public constructor() {
    this.coordinates = new Map<string, Coordinates[]>();
    this.elements = new Map<string, ElementData>();
    this.subscribers = new Map<string, ISubscriber>();
  }

  public add(subscriber: ISubscriber): void {
    const id = subscriber.getSubscriberId();
    this.elements.set(id, [subscriber.getSubscriberElement(), false]);
    this.coordinates.set(id, []);
    this.subscribers.set(id, subscriber);
  }

  public remove(subscriber: ISubscriber): void {
    const id = subscriber.getSubscriberId();
    this.subscribers.delete(id);
    const [element] = this.elements.get(id);
    element.removeEventListener("click", this.setCoordinates.bind(this, id));
    this.elements.delete(id);
    this.coordinates.delete(id);
  }

  public notify(): void {
    this.subscribers.forEach((subscriber) => {
      if (this.coordinates.get(subscriber.getSubscriberId()).length !== 0) {
        subscriber.update();
      }
    });
  }

  public connect(): void {
    this.elements.forEach((elementData, id) => {
      const [element, isAttached] = elementData;
      if (isAttached) {
        return;
      }
      element.removeEventListener("click", this.setCoordinates.bind(this, id));
      element.addEventListener("click", this.setCoordinates.bind(this, id));
    });
  }

  public hasCoordinates(subscriber: ISubscriber): boolean {
    return (
      this.coordinates.size !== 0 &&
      this.coordinates.get(subscriber.getSubscriberId()).length !== 0
    );
  }

  private setCoordinates(id: string, e: MouseEvent) {
    this.coordinates.get(id).push([e.offsetX, e.offsetY]);
    this.notify();
  }

  public getCoordinates(subscriber: ISubscriber): Coordinates {
    return this.coordinates.get(subscriber.getSubscriberId()).shift();
  }
}
