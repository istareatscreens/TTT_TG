import { v4 as uuidv4 } from "uuid";

export class UniqueId {
  private Id: string;

  public constructor() {
    this.Id = uuidv4();
  }

  public getId(): string {
    return this.Id;
  }
}
