import {Component, effect, model, OnInit, viewChild, CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {ObjetService} from '../../shared/services/objet.service';
import {IObjet} from '../../shared/interfaces/i-objet';
import {IMedia} from '../../shared/interfaces/i-media';
import {DatePipe} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {BootstrapModalComponent} from '../subs/bootstrap-modal/bootstrap-modal.component';


@Component({
  selector: 'app-page-list-objets',
  imports: [
    DatePipe,
    FormsModule,
    RouterLink,
    BootstrapModalComponent
  ],
  templateUrl: './page-list-objets.component.html',
  styleUrl: './page-list-objets.component.scss',
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class PageListObjetsComponent implements OnInit {

  allListObjets: IObjet[] = [];

  idUser: number = 1; // This should be dynamically set based on the logged-in user
  textFilter = model<string>('');

  modalDelete = viewChild.required<BootstrapModalComponent>('modalDelete');

  filteredList: IObjet[] = [];

  public constructor(private objetService: ObjetService) {

    effect(() => {
      // This effect will run whenever textFilter changes
      const filterText = this.textFilter().trim().toLowerCase();
      if (!filterText || filterText.length === 0) {
        this.filteredList = this.allListObjets; // Reset to all objets if filter is empty

      } else {
        this.filteredList = this.allListObjets.filter(objet =>
          objet.Nom.toLowerCase().includes(filterText)
        );
      }
    });

  }

  ngOnInit(): void {

    this.objetService.getAllObjetsOfProprietaire(this.idUser).subscribe(
      {
        next: (response) => {
          if (response.result) {
            this.allListObjets = response.content.data;
            this.filteredList = this.allListObjets; // Initialize filtered list with all objets
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

    if (!primaryMedia) {
      return ""; // Return empty string if no primary media found
    }

    if (primaryMedia?.Type === 'DIRECT_LINK_IMG') {
      return primaryMedia.UriServeur;
    } else if (primaryMedia?.Type === 'IMAGE') {
      return `${primaryMedia.UriServeur}`;
    }

    return ""; // Return empty string for unsupported media types

  }

  isUserProprietaire(objet: IObjet) {
    const existOwner = objet.Proprietaire.find(owner => owner.Id_Proprietaire === this.idUser);
    return existOwner !== undefined;
  }

  filterObjets($event: Event) {
    console.log($event);
  }

  onDeleteObjet(objetRef: IObjet) {

    this.modalDelete().body = `Êtes-vous sûr de vouloir supprimer ${objetRef.Nom} ?`;
    this.modalDelete().clickOk.subscribe(
      () => {
        this.deleteObjet(objetRef, () => {
          this.modalDelete().close();
        });
      }
    );

    this.modalDelete().open()

  }

  private deleteObjet(objetRef: IObjet, afterDelete?: () => void) {
    this.objetService.deleteObjet(objetRef.Id_Objet).subscribe({
        next: (response) => {
          if (response.result) {
            // Remove the deleted objet from the list
            this.allListObjets = this.allListObjets.filter(objet => objet.Id_Objet !== objetRef.Id_Objet);
            this.filteredList = this.filteredList.filter(objet => objet.Id_Objet !== objetRef.Id_Objet);

            if (afterDelete) {
              afterDelete();
            }

          } else {
            console.error('Failed to delete objet:', response.error);
          }
        },
        error: (error) => {
          console.error('Error deleting objet:', error);
        }
      }

    );

  }
}
