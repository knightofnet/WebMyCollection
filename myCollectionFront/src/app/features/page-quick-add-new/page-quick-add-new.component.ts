import {
  Component,
  computed,
  effect,
  ElementRef,
  OnInit,
  Signal,
  signal,
  viewChild,
  WritableSignal
} from '@angular/core';
import {PhotoPayloadComponent} from '../subs/photo-payload/photo-payload.component';
import {ReactiveFormsModule} from '@angular/forms';
import {ImageStoreService} from '../../shared/services/image-store.service';
import {AsyncPipe} from '@angular/common';
import {MatAutocomplete, MatAutocompleteTrigger, MatOption} from '@angular/material/autocomplete';
import {Observable, of, switchMap} from 'rxjs';
import {ObjetService} from '../../shared/services/objet.service';
import {NgSelectComponent} from '@ng-select/ng-select';
import {ICategorie} from '../../shared/interfaces/i-categorie';
import {CategorieService} from '../../shared/services/categorie.service';
import {IParamForCreateOrUpdateObjet} from '../../shared/interfaces/side/i-param-for-create-or-update-objet';
import {IPhotoPayload} from '../../shared/interfaces/subs/i-photo-payload';


@Component({
  selector: 'app-page-quick-add-new',
  imports: [
    PhotoPayloadComponent,
    ReactiveFormsModule,
    AsyncPipe,
    MatAutocompleteTrigger,
    MatAutocomplete,
    MatOption,
    NgSelectComponent
  ],
  templateUrl: './page-quick-add-new.component.html',
  styleUrl: './page-quick-add-new.component.scss'
})
export class PageQuickAddNewComponent implements OnInit {

  photoSelected: Signal<string | null> = signal(null);

  inputNameElement = viewChild<ElementRef<HTMLInputElement>>('inputName');

  currentZone = signal(0);
  zones: string[] = ['zone-photos', 'zone-preview-photos', 'zone-nom', 'zone-details', 'zone-final'];

  filteredObjectNames: Observable<string[]> = new Observable<string[]>();
  lastObjects: string[] = [];

  keywords: ICategorie[] = [];
  categories: ICategorie[] = [];

  newObject: IParamForCreateOrUpdateObjet = {
    nom: null,
    description: null,
    categories: [],
    keywords: [],
    imageMode: 'none',
    imageFile: null,
    imageUrl: null,
    idProprietaire: []
  }
  private photoSelectedInfos: WritableSignal<IPhotoPayload | null> = signal(null);


  public constructor(
    private objetService: ObjetService,
    private categorieServices: CategorieService,
    private imageStoreService: ImageStoreService) {


    effect((ecr) => {


      if (this.currentZone()) {
        console.log("Zone", this.currentZone());
        this.updateInfosOfView(this.currentZone())
      }


    });


  }


  ngOnInit() {
    this.objetService.getLastAddedObject(5).subscribe({
      next: results => {
        if (results.result) {

          const dicoWordsLastObject = new Map<string, number>();

          for (const objDto of results.content.data) {
            for (const word of objDto.Nom.split(' ')) {
              dicoWordsLastObject.set(word, (dicoWordsLastObject.get(word) ?? 0) + 1);
            }
          }

          // Filtrer les mots avec une fréquence >= 1
          this.lastObjects = Array.from(dicoWordsLastObject.entries())
            .filter(([_, count]) => count >= 2)
            .sort((a, b) => b[1] - a[1])
            .map(([word]) => word);

        }
      }
    })

    this.categorieServices.getAllCategories(-1, -1).subscribe({
      next: catResponse => {
        if (catResponse.result) {
          this.categories = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 1);
          this.keywords = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 2);
        } else {
          console.error('Failed to fetch categories:', catResponse.error);
        }
      }
    })


  }

  prevZone() {
    if (this.currentZone() > 0) {
      this.currentZone.update(v => v - 1);


    }

  }

  nextZone(isForce = false) {

    console.log("NextZone")

    if (this.currentZone() < this.zones.length - 1) {

      const currZone = this.currentZone();
      const isOkToGoNext: boolean = this.saveZoneInfos(currZone);

      console.log("isOkToGoNext : ", isOkToGoNext);

      if (isForce || isOkToGoNext) {

        this.currentZone.update(v => v + 1);

      }
    }


  }

  onPhotoSelected($event: IPhotoPayload) {

    console.log('Photo selected in quick add new:', $event);

    if ($event) {

      this.photoSelected = computed(() => this.imageStoreService.objectUrl());
      this.photoSelectedInfos.update(() => $event);

      this.nextZone(true);

    }


  }

  getNamesFromServer() {
    const input = this.inputNameElement()!.nativeElement;

    const value = input.value;

    if (value.length > 2) {

      this.filteredObjectNames = this.objetService.getNamesLike(value)
        .pipe(
          switchMap((result) => {
            if (result.result) {
              return of(result.content.data)
            }
            return of([]);
          })
        );
    }

  }


  addToInputName(str: string) {

    let inputElement = this.inputNameElement()!.nativeElement;
    console.log("selection", inputElement.selectionStart)
    console.log("selection-END", inputElement.selectionEnd);

    const ixStart = inputElement.selectionStart || 0;
    const ixEnd = inputElement.selectionEnd || 0;
    const lenght = inputElement.value.length || 0;


    if (ixStart === ixStart) {

      if (ixStart === 0) {
        str = str + inputElement.value;
      } else if (ixStart === lenght - 1) {
        str = inputElement.value + str;
      } else {

        str = inputElement.value.substring(0, ixStart) + str + inputElement.value.substring(ixEnd);

      }

    } else {
      str = str = inputElement.value.substring(0, ixStart) + str + inputElement.value.substring(ixEnd);
    }

    inputElement.value = str;

    inputElement.focus();
    this.getNamesFromServer();
  }


  addNewKeyword($event: any): void {

    console.log('Adding new keyword:', $event);


    const label = $event.Nom.trim();
    if (!label) return;

    const isAlreadyExists = this.keywords.some(keyword => keyword.Nom.toLowerCase() === label.toLowerCase());
    if (isAlreadyExists) {
      console.debug('Keyword already exists:', label);
      return;
    }

    const newKeyword: ICategorie = {Id_Categorie: -Date.now(), Nom: label, Id_TyCategorie: 2};
    this.keywords.push(newKeyword);
    this.keywords = [...this.keywords];

    console.log('New keyword added:', $event, newKeyword);

    //const current = this.objectForm.value.keywords ?? [];
    //this.objectForm.patchValue({keywords: [...current, newKeyword]});
  }

  private saveZoneInfos(zoneId: number): boolean {
    console.log("Save infos for zone", zoneId);
    let isOkToGoNext: boolean = false;
    switch (zoneId) {
      case 0 :
        isOkToGoNext = this.photoSelected() != null;
        console.log("Photo selected?", isOkToGoNext, this.photoSelected());
        break;
      case 1 :
        if (this.photoSelected() != null && this.photoSelectedInfos()) {
          this.newObject.imageMode = 'upload';
          const blob = this.imageStoreService.blob();
          if (!blob) {
            console.error('Blob not found', zoneId);
          }
          const mimeType = this.photoSelectedInfos()!.mimeType;
          this.newObject.imageFile = new File([blob!], this.photoSelectedInfos()!.imgName!, {type: mimeType!});
          isOkToGoNext = true;
        }
        break;
      case 3 :
        isOkToGoNext = true;
        break;
      case 2 :
        const newName = this.inputNameElement()!.nativeElement.value;
        if (newName.length > 2) {
          this.newObject.nom = newName;
          isOkToGoNext = true;
        }

        break;

    }

    return isOkToGoNext;
  }

  private updateInfosOfView(zoneIx: number) {
    switch (zoneIx) {
      case 2 :
        if (this.inputNameElement() && this.newObject.nom) {
          console.log("Set input name to ", this.newObject.nom);
          this.inputNameElement()!.nativeElement.value = this.newObject.nom;
        }
    }
  }

  onSubmit() {


    this.objetService.addNewObjet(this.newObject).subscribe({
        next: (response) => {
          if (response.result) {
            console.log("OK", response.content.data);
          }
        }
      }
    );
  }
}
