import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ICategorie} from '../interfaces/i-categorie';
import {Observable} from 'rxjs';
import {IGenResponse} from '../../core/interfaces/i-genresponse';

@Injectable({
  providedIn: 'root'
})
export class CategorieService {

  constructor(private httpClient: HttpClient) { }

  getAllCategories(page: number = -1, size: number = -1, typeCategorie : number = -1): Observable<IGenResponse<ICategorie[]>> {
    // If page and size are -1, we fetch all categories
    return this.httpClient.get<IGenResponse<ICategorie[]>>(`/api/v1/categorie/getAll?page=${page}&size=${size}&typeCategorie=${typeCategorie}`);
  }
}
