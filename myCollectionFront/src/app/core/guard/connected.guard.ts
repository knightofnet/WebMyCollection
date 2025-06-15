import {inject} from '@angular/core';
import {CanActivateFn, Router} from '@angular/router';
import {AuthService} from '../services/auth.service';
import {catchError, map, of} from 'rxjs';

export const connectedGuard: CanActivateFn = (route, state) => {

  const router = inject(Router);

  return inject(AuthService).isAuthenticatedAsync().pipe(
    map(response => {
      if (response && response.result) {

        return true;

      }

      console.error("Error checking authentication", response);
      router.navigate(['/login'], {queryParams: {returnUrl: state.url}});
      return false; // Return false if there's an error
    }),
    catchError((error) => {
      console.error("Error checking authentication", error);
      router.navigate(['/login'], {queryParams: {returnUrl: state.url}});
      return of(false); // Return false if there's an error
    })
  )
};
