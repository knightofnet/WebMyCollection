import {Component, OnInit} from '@angular/core';
import {ObjetService} from '../../shared/services/objet.service';
import {IObjet} from '../../shared/interfaces/i-objet';
import { IMedia } from '../../shared/interfaces/i-media';
import {DatePipe} from '@angular/common';


@Component({
  selector: 'app-page-list-objets',
  imports: [
    DatePipe
  ],
  templateUrl: './page-list-objets.component.html',
  styleUrl: './page-list-objets.component.scss'
})
export class PageListObjetsComponent implements OnInit {

  listObjets: IObjet[] = [];

  idUser: number = 1; // This should be dynamically set based on the logged-in user

  public constructor(private objetService: ObjetService) {
    // Inject the service to use it later
  }

  ngOnInit(): void {

    this.objetService.getAllObjetsOfProprietaire(this.idUser).subscribe(
      {
        next: (response) => {
          if (response.result) {
            this.listObjets = response.content.data;
          }
        },
        error: (error) => {
          console.error('Error fetching objets:', error);
        }
      }
    );

  }


  getPrimaryMediaUrl(Medias: IMedia[]) {
    const primaryMedia = Medias.find(media => media.EstPrincipal);
    return primaryMedia ? primaryMedia.UriServeur : "";
  }

  isUserProprietaire(objet: IObjet) {
    const existOwner = objet.Proprietaire.find(owner => owner.Id_Proprietaire === this.idUser);
    return existOwner !== undefined;
  }
}
