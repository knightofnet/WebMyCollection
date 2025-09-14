import {Component, computed, ElementRef, OnInit, signal, viewChild} from '@angular/core';
import {PhotoPayloadComponent} from '../subs/photo-payload/photo-payload.component';
import {ReactiveFormsModule} from '@angular/forms';
import {ImageStoreService} from '../../shared/services/image-store.service';
import {AsyncPipe} from '@angular/common';
import {MatAutocomplete, MatAutocompleteTrigger, MatOption} from '@angular/material/autocomplete';
import {Observable, of, switchMap} from 'rxjs';
import {ObjetService} from '../../shared/services/objet.service';
import {NgSelectComponent} from '@ng-select/ng-select';


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

  photoSelected: any;

  inputNameElement = viewChild.required<ElementRef<HTMLInputElement>>('inputName');

  currentZone = signal(0);
  zones: string[] = ['zone-photos', 'zone-preview-photos', 'zone-nom', 'zone-details', 'zone-final'];
  filteredObjectNames: Observable<string[]> = new Observable<string[]>();
  lastObjects: string[] = [];


  public constructor(
    private objetService: ObjetService,
    private imageStoreService: ImageStoreService) {

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
  }

  prevZone() {
    if (this.currentZone() > 0) {
      this.currentZone.update(v => v - 1);
      this.scrollToZone();
    }

  }

  nextZone() {
    if (this.currentZone() < this.zones.length - 1) {
      this.currentZone.update(v => v + 1);
      this.scrollToZone();
    }

  }

  scrollToZone() {
    const zoneId = this.zones[this.currentZone()];
    const el = document.getElementById(zoneId);
    if (el) {
      el.scrollIntoView({behavior: 'smooth'});
    }
  }

  onPhotoSelected($event: boolean) {

    console.log('Photo selected in quick add new:', $event);

    if ($event) {

      this.photoSelected = computed(() => this.imageStoreService.objectUrl());

      this.nextZone();
    }


  }

  getNamesFromServer() {
    const input = this.inputNameElement().nativeElement;

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

    let inputElement = this.inputNameElement().nativeElement;
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
}
