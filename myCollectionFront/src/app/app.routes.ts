import { Routes } from '@angular/router';
import {PageListObjetsComponent} from './features/page-list-objets/page-list-objets.component';
import {PageAddNewObjetComponent} from './features/page-add-new-objet/page-add-new-objet.component';
import {PageLoginComponent} from './features/pages-login/page-login/page-login.component';

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
    path : 'login/:token',
    component : PageLoginComponent
  },
  {
    path : 'login',
    component : PageLoginComponent
  },
  {
    path : '**',
    redirectTo: 'list',
  }

];
