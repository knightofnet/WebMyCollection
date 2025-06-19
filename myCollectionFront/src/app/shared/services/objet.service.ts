import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { IObjet } from '../interfaces/i-objet';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {Observable} from 'rxjs';
import {ICategorie} from '../interfaces/i-categorie';
import {IParamForCreateOrUpdateObjet} from '../interfaces/side/i-param-for-create-or-update-objet';
import {ApiService} from '../../core/services/ApiService';
import {IMedia} from '../interfaces/i-media';




@Injectable({
  providedIn: 'root'
})
export class ObjetService {
  addMediaForObjet(payload: { imageMode: string ; imageFile: File | null ; imageUrl: string | null ; idObjet: number | null; }): Observable<IGenResponse<IMedia>> {

    const formData: FormData = new FormData();

    if (payload.imageFile && payload.imageMode === 'upload') {
      formData.append("file", payload.imageFile, payload.imageFile.name);
    }

    formData.append("data", JSON.stringify({
      imageMode: payload.imageMode,
      imageUrl: payload.imageUrl,
      idObjet: payload.idObjet
    }));

    return this.apiService.post<IGenResponse<IMedia>>('/api/v1/objet/addMediaForObjet', formData);
  }

  constructor(private apiService: ApiService) {
  }

  getAllObjetsOfProprietaire(idProprietaire: number): Observable<IGenResponse<IObjet[]>> {
    return this.apiService.getWithCredential<IGenResponse<IObjet[]>>(`/api/v1/objet/getAll/${idProprietaire}`);
  }

  addNewObjet(param: IParamForCreateOrUpdateObjet): Observable<IGenResponse<boolean>> {

    console.log('Param', param);


    const formData: FormData = new FormData();

    if (param.imageFile && param.imageMode === 'upload') {
      console.log(param.imageFile);
      formData.append("file", param.imageFile, param.imageFile.name);
    }

    formData.append("data", JSON.stringify(param));

    return this.apiService.post<IGenResponse<boolean>>('/api/v1/objet/addNewObjet', formData);

  }

  getObjetById(idObjet: number) : Observable<IGenResponse<IObjet>> {
    return this.apiService.get<IGenResponse<IObjet>>(`/api/v1/objet/getById/${idObjet}`);
  }

  updateObjet(idObjet: number, params: IParamForCreateOrUpdateObjet) : Observable<IGenResponse<boolean>> {
    const data = {
      idObjet: idObjet,
      data : params
    };
    return this.apiService.put<IGenResponse<boolean>>('/api/v1/objet/updateObjet', data);
  }

  deleteObjet(idObjet: number) : Observable<IGenResponse<boolean>> {
    const data = {
      idObjet: idObjet,
    }
    return this.apiService.delete<IGenResponse<boolean>>('/api/v1/objet/deleteObjet',data);
  }

  deleteMediaForObjet(idMedia: number) : Observable<IGenResponse<boolean>> {

    const data = {
      idMedia: idMedia,
    }
    return this.apiService.delete<IGenResponse<boolean>>('/api/v1/objet/deleteMediaForObjet', data);

  }
}
