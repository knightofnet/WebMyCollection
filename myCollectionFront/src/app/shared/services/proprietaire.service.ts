import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {IProprietaire} from '../interfaces/i-proprietaire';
import {Observable} from 'rxjs';
import {ICategorie} from '../interfaces/i-categorie';

@Injectable({
  providedIn: 'root'
})
export class ProprietaireService {

  constructor(private httpClient: HttpClient) {
  }


  getAllProprietaires(): Observable<IGenResponse<IProprietaire[]>> {
    return this.httpClient.get<IGenResponse<IProprietaire[]>>('/api/v1/proprietaire/getAll');

  }



}
