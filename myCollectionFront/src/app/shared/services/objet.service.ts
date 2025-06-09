import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { IObjet } from '../interfaces/i-objet';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {Observable} from 'rxjs';
import {ICategorie} from '../interfaces/i-categorie';


@Injectable({
  providedIn: 'root'
})
export class ObjetService {

  constructor(private httpClient: HttpClient) {
  }

  getAllObjetsOfProprietaire(idProprietaire: number): Observable<IGenResponse<IObjet[]>> {



    return this.httpClient.get<IGenResponse<IObjet[]>>(`/api/v1/objet/getAll/${idProprietaire}`);
  }

  addNewObjet(param: {
    nom: string | null | undefined;
    description: string | null | undefined;
    categories: ICategorie[] | null | undefined;
    keywords: ICategorie[] | null | undefined;
    idProprietaire: number[] | null | undefined;
    imageMode: string | null | undefined;
    imageFile: File | null | undefined;
    imageUrl: string | null | undefined
  }): Observable<IGenResponse<boolean>> {

    console.log('Param', param);


    const formData: FormData = new FormData();

    if (param.imageFile && param.imageMode === 'upload') {
      console.log(param.imageFile);
      formData.append("file", param.imageFile, param.imageFile.name);
    }

    formData.append("data", JSON.stringify(param));

    return this.httpClient.post<IGenResponse<boolean>>('/api/v1/objet/addNewObjet', formData);

  }
}
