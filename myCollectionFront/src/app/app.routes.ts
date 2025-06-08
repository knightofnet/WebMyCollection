import { Routes } from '@angular/router';
import {PageListObjetsComponent} from './features/page-list-objets/page-list-objets.component';

export const routes: Routes = [
  {
    path : 'list',
    component : PageListObjetsComponent
  },
  {
    path : '**',
    redirectTo: 'list',
  }

];
