import {IErrorResponse} from './i-errorresponse';

export interface IGenResponse<T> {

  result:boolean,
  content:{
    type:string,
    data:T
  },
  error?:IErrorResponse;
}
