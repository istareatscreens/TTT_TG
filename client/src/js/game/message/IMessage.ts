export interface IMessageIn {}
export interface IMessageOut {}

export class IMessage {
  public setMessageIn(message: IMessageOut) {}
  public getMessageIn(message: IMessageOut) {}
  public getMessageOut(message: IMessageIn) {}
  public setMessageOut(message: IMessageIn) {}
}
