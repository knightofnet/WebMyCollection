import {Observable} from 'rxjs';
import {Injectable} from '@angular/core';
import {environment} from '../../../environments/environment';
import {HttpClient} from '@angular/common/http';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private prefix = environment.apiPrefix;


  constructor(private http: HttpClient) {}

  get<T>(endpoint: string): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');

    return this.http.get<T>(`${endpoint}`);
  }

  getWithCredential<T>(endpoint: string): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');
    const options = {
      withCredentials: true
    };
    return this.http.get<T>(`${endpoint}`, options);
  }

  post<T>(endpoint: string, body: any): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');
    return this.http.post<T>(`${endpoint}`, body);
  }

  put<T>(endpoint: string, body: any): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');
    return this.http.put<T>(`${endpoint}`, body);
  }

  delete<T>(endpoint: string, body:any): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');
    return this.http.delete<T>(`${endpoint}`, { body });
  }

  patch<T>(endpoint: string, body: any): Observable<T> {
    endpoint= endpoint.replace('/api/v1/', this.prefix + '/');
    return this.http.patch<T>(`${endpoint}`, body);
  }


}
