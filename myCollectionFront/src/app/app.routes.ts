import { Routes } from '@angular/router';
import {PageListObjetsComponent} from './features/page-list-objets/page-list-objets.component';
import {PageAddNewObjetComponent} from './features/page-add-new-objet/page-add-new-objet.component';

export const routes: Routes = [
  {
    path : 'list',
    component : PageListObjetsComponent
  },
  {
    path : 'addNew',
    component : PageAddNewObjetComponent
  },
  {
    path : 'editOne/:id',
    component : PageAddNewObjetComponent
  },
  {
    path : '**',
    redirectTo: 'list',
  }

];
