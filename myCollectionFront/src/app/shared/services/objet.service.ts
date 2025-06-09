import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { IObjet } from '../interfaces/i-objet';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {Observable} from 'rxjs';
import {ICategorie} from '../interfaces/i-categorie';
import {IParamForCreateOrUpdateObjet} from '../interfaces/side/i-param-for-create-or-update-objet';




@Injectable({
  providedIn: 'root'
})
export class ObjetService {

  constructor(private httpClient: HttpClient) {
  }

  getAllObjetsOfProprietaire(idProprietaire: number): Observable<IGenResponse<IObjet[]>> {



    return this.httpClient.get<IGenResponse<IObjet[]>>(`/api/v1/objet/getAll/${idProprietaire}`);
  }

  addNewObjet(param: IParamForCreateOrUpdateObjet): Observable<IGenResponse<boolean>> {

    console.log('Param', param);


    const formData: FormData = new FormData();

    if (param.imageFile && param.imageMode === 'upload') {
      console.log(param.imageFile);
      formData.append("file", param.imageFile, param.imageFile.name);
    }

    formData.append("data", JSON.stringify(param));

    return this.httpClient.post<IGenResponse<boolean>>('/api/v1/objet/addNewObjet', formData);

  }

  getObjetById(idObjet: number) : Observable<IGenResponse<IObjet>> {
    return this.httpClient.get<IGenResponse<IObjet>>(`/api/v1/objet/getById/${idObjet}`);
  }

  updateObjet(idObjet: number, params: IParamForCreateOrUpdateObjet) : Observable<IGenResponse<boolean>> {
    const data = {
      idObjet: idObjet,
      data : params
    };
    return this.httpClient.put<IGenResponse<boolean>>('/api/v1/objet/updateObjet', data);
  }
}
