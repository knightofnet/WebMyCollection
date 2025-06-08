import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { IObjet } from '../interfaces/i-objet';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {Observable} from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class ObjetService {

  constructor(private httpClient: HttpClient) {
  }

  getAllObjetsOfProprietaire(idProprietaire: number): Observable<IGenResponse<IObjet[]>> {

    return this.httpClient.get<IGenResponse<IObjet[]>>(`/api/v1/objet/getAll/${idProprietaire}`);
  }

}
