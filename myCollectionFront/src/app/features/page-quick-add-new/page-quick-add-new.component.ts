import {
  Component,
  computed,
  effect,
  ElementRef,
  inject,
  OnInit,
  Signal,
  signal,
  viewChild,
  WritableSignal
} from '@angular/core';
import {PhotoPayloadComponent} from '../subs/photo-payload/photo-payload.component';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {ImageStoreService} from '../../shared/services/image-store.service';
import {AsyncPipe} from '@angular/common';
import {MatAutocomplete, MatAutocompleteTrigger, MatOption} from '@angular/material/autocomplete';
import {forkJoin, Observable, of, switchMap} from 'rxjs';
import {ObjetService} from '../../shared/services/objet.service';
import {NgSelectComponent} from '@ng-select/ng-select';
import {ICategorie} from '../../shared/interfaces/i-categorie';
import {CategorieService} from '../../shared/services/categorie.service';
import {IParamForCreateOrUpdateObjet} from '../../shared/interfaces/side/i-param-for-create-or-update-objet';
import {IPhotoPayload} from '../../shared/interfaces/subs/i-photo-payload';
import {MatSnackBar} from '@angular/material/snack-bar';
import {IGenResponse} from '../../core/interfaces/i-genresponse';
import {IObjet} from '../../shared/interfaces/i-objet';
import {Router} from '@angular/router';


@Component({
  selector: 'app-page-quick-add-new',
  imports: [
    PhotoPayloadComponent,
    ReactiveFormsModule,
    AsyncPipe,
    MatAutocompleteTrigger,
    MatAutocomplete,
    MatOption,
    NgSelectComponent,
    FormsModule

  ],
  templateUrl: './page-quick-add-new.component.html',
  styleUrl: './page-quick-add-new.component.scss'
})
export class PageQuickAddNewComponent implements OnInit {


  photoSelected: Signal<string | null> = signal(null);

  inputNameElement = viewChild<ElementRef<HTMLInputElement>>('inputName');
  textAreaDescriptionElement = viewChild<ElementRef<HTMLTextAreaElement>>('textAreaDescription');

  currentZone = signal(0);
  zones: string[] = ['zone-photos', 'zone-preview-photos', 'zone-nom', 'zone-details', 'zone-final'];

  filteredObjectNames: Observable<string[]> = new Observable<string[]>();
  lastObjects: string[] = [];

  keywords: ICategorie[] = [];
  categories: ICategorie[] = [];
  lastCategories: ICategorie[] = [];

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

  private static readonly REGEX_SPLIT_NAME = /[\s-_.]+/;

  private _snackBar = inject(MatSnackBar);

  private snackBarDuration = 10;


  public constructor(
    private objetService: ObjetService,
    private categorieServices: CategorieService,
    private imageStoreService: ImageStoreService,
    private router: Router
  ) {

    effect((ecr) => {

      if (this.currentZone()) {
        console.log("Zone", this.currentZone());
        this.updateInfosOfView(this.currentZone())
      }

    });

  }


  ngOnInit() {

    const getLastAddedObject$: Observable<IGenResponse<IObjet[]>> = this.objetService.getLastAddedObject(5);
    const getAllCategories$: Observable<IGenResponse<ICategorie[]>> = this.categorieServices.getAllCategories(-1, -1);
    const getLastestCategories$: Observable<IGenResponse<ICategorie[]>> = this.categorieServices.getLastCategories(5);

    const subscriptions = [getLastAddedObject$, getAllCategories$, getLastestCategories$];

    const forkJoined = forkJoin(subscriptions) as Observable<[IGenResponse<IObjet[]>, IGenResponse<ICategorie[]>, IGenResponse<ICategorie[]>]>;

    forkJoined.subscribe({
      next: (result) => {

        const objResponse: IGenResponse<IObjet[]> = result[0];
        const catResponse: IGenResponse<ICategorie[]> = result[1];
        const lastCatResponse: IGenResponse<ICategorie[]> = result[2];

        // Traitement de la réponse des objets
        if (objResponse.result) {

          const dicoWordsLastObject = new Map<string, number>();

          for (const objDto of objResponse.content.data) {

            for (const word of objDto.Nom.split(PageQuickAddNewComponent.REGEX_SPLIT_NAME)) {
              dicoWordsLastObject.set(word, (dicoWordsLastObject.get(word) ?? 0) + 1);
            }
          }

          // Filtrer les mots avec une fréquence >= 1
          this.lastObjects = Array.from(dicoWordsLastObject.entries())
            .filter(([_, count]) => count >= 2)
            .sort((a, b) => b[1] - a[1])
            .map(([word]) => word);

        }

        // Traitement de la réponse des catégories et mots clés
        if (catResponse.result) {
          this.categories = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 1);
          this.keywords = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 2);
        } else {
          console.error('Failed to fetch categories:', catResponse.error);
        }

        // Traitement de la réponse des dernières catégories
        if (lastCatResponse.result) {
          console.log('Last CatResponse', lastCatResponse);
          this.lastCategories = lastCatResponse.content.data;
        } else {
          console.error('Failed to fetch last categories:', lastCatResponse.error);
        }
      },
      error: (err) => {
        console.error('Error during forkJoin:', err);
      }
    });


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
      const isOkToGoNext: boolean = this.saveZoneInfos(currZone, isForce);

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

  getNamesLikeInputNameFromBdd() {
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
    const ixStart = inputElement.selectionStart || 0;
    const ixEnd = inputElement.selectionEnd || 0;
    const value = inputElement.value;
    const length = value.length;

    let newValue: string;
    let newSelectionPos: number;

    if (ixStart === ixEnd) {
      if (length === 0) {
        newValue = str;
        newSelectionPos = str.length;
      } else if (ixStart === 0) {
        newValue = str + ' ' + value;
        newSelectionPos = str.length + 1;
      } else if (ixStart === length) {
        newValue = value + ' ' + str;
        newSelectionPos = newValue.length;
      } else {
        newValue = value.slice(0, ixStart) + ' ' + str + ' ' + value.slice(ixEnd);
        newSelectionPos = ixStart + str.length + 2;
      }
    } else {
      newValue = value.slice(0, ixStart) + ' ' + str + ' ' + value.slice(ixEnd);
      newSelectionPos = ixStart + str.length + 2;
    }

    inputElement.value = newValue.trim().replace(/\s+/g, ' ');
    inputElement.focus();
    inputElement.setSelectionRange(newSelectionPos, newSelectionPos);
    this.getNamesLikeInputNameFromBdd();
  }


  addCategoryToSelect(category: any) {

    if (category && category.Id_Categorie) {


      if (!this.newObject.categories!.find(cat => cat.Id_Categorie === category.Id_Categorie)) {
        this.newObject.categories!.push(category);
        this.newObject.categories = [...this.newObject.categories!];
      }
    } else {
      if (category && category.Nom) {
        const label = category.Nom.trim();
        if (!label) return;

        const isAlreadyExists = this.categories.some(cat => cat.Nom.toLowerCase() === label.toLowerCase());
        if (isAlreadyExists) {
          console.debug('Category already exists:', label);
          return;
        }

        const newCategory: ICategorie = {Id_Categorie: -Date.now(), Nom: label, Id_TyCategorie: 1};
        this.categories.push(newCategory);
        this.categories = [...this.categories];

        this.newObject.categories!.push(newCategory);
        this.newObject.categories = [...this.newObject.categories!];

        console.log('New category added:', category, newCategory);
      }
    }

  }

  addNewKeywordToSelect(keyword: any) {
    if (keyword && keyword.Id_Categorie) {


      if (!this.newObject.keywords!.find(cat => cat.Id_Categorie === keyword.Id_Categorie)) {
        this.newObject.keywords!.push(keyword);
        this.newObject.keywords = [...this.newObject.keywords!];
      }
    } else {
      if (keyword && keyword.Nom) {
        const label = keyword.Nom.trim();
        if (!label) return;

        const isAlreadyExists = this.keywords.some(cat => cat.Nom.toLowerCase() === label.toLowerCase());
        if (isAlreadyExists) {
          console.debug('Keyword already exists:', label);
          return;
        }

        const newKeyword: ICategorie = {Id_Categorie: -Date.now(), Nom: label, Id_TyCategorie: 1};
        this.keywords.push(newKeyword);
        this.keywords = [...this.keywords];

        this.newObject.keywords!.push(newKeyword);
        this.newObject.keywords = [...this.newObject.keywords!];

        console.log('New keywords added:', keyword, newKeyword);
      }
    }
  }
  private saveZoneInfos(zoneId: number, isForce: boolean): boolean {
    console.log("Save infos for zone", zoneId);
    let isOkToGoNext: boolean = false;
    switch (zoneId) {
      case 0 :
        isOkToGoNext = this.photoSelected() != null && this.photoSelectedInfos() != null;
        if (!isOkToGoNext && !isForce) {
          this._snackBar.open("Une image est obligatoire", "OK", {duration: this.snackBarDuration * 1000});

        }
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
        if (this.textAreaDescriptionElement) {
          const desc = this.textAreaDescriptionElement()!.nativeElement.value;
          if (desc.length > 2) {
            this.newObject.description = desc;
          }
        }

        isOkToGoNext = true;

        break;
      case 2 :
        const newName = this.inputNameElement()!.nativeElement.value;
        if (newName.length > 2) {
          this.newObject.nom = newName;
          isOkToGoNext = true;
        } else {
          this._snackBar.open("Le nom est obligatoire", "OK", {duration: this.snackBarDuration * 1000});
        }

        break;

    }

    return isOkToGoNext;
  }

  private updateInfosOfView(zoneIx: number) {
    switch (zoneIx) {
      case 2 :
        if (this.inputNameElement() && this.newObject.nom) {

          this.inputNameElement()!.nativeElement.value = this.newObject.nom;
        }
        break;

      case 3 :
        if (this.textAreaDescriptionElement && this.newObject.description) {

          this.textAreaDescriptionElement()!.nativeElement.value = this.newObject.description;
        }
        break;
    }
  }

  onSubmit() {


    this.objetService.addNewObjet(this.newObject).subscribe({
        next: (response) => {
          if (response.result) {
            console.log("OK", response.content.data);

            const sb = this._snackBar.open("Objet ajouté avec succès", "OK", {duration: this.snackBarDuration * 1000});
            sb.onAction().subscribe(
              () => {
                this.router.navigate(['/list'], {queryParams: {refresh: Date.now()}});
              }
            )
          }
        }
      }
    );
  }

  compareCategories(cat1: ICategorie, cat2: ICategorie) {
    return cat1 && cat2 && cat1.Id_Categorie === cat2.Id_Categorie;
  }


  onInputNameKeyUp($event: KeyboardEvent) {
    if ($event.key === 'Enter') {
      this.nextZone();
    } else {
      this.getNamesLikeInputNameFromBdd();
    }
  }


}
