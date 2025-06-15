import {Injectable} from '@angular/core';
import {ApiService} from './ApiService';
import {Observable} from 'rxjs';
import {IGenResponse} from '../interfaces/i-genresponse';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private apiService: ApiService) {
  }

  tryLogin(username: string, codepin: string): Observable<IGenResponse<boolean>> {
    return this.apiService.post<IGenResponse<boolean>>('/api/v1/auth/login',
      {username: username, pin: codepin}
    );
  }

  validateLoginToken(token: string) : Observable<IGenResponse<boolean>> {
    return this.apiService.post<IGenResponse<boolean>>('/api/v1/auth/validateToken',
      {token: token}
    );
  }


}
