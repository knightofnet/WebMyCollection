import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import {faCheck, faPlus, faSave} from '@fortawesome/free-solid-svg-icons';
import {ICategorie} from '../../shared/interfaces/i-categorie';
import {IProprietaire} from '../../shared/interfaces/i-proprietaire';
import {CategorieService} from '../../shared/services/categorie.service';
import {ProprietaireService} from '../../shared/services/proprietaire.service';
import * as bootstrap from 'bootstrap';
import {
  NgLabelTemplateDirective,
  NgOptionComponent,
  NgOptionTemplateDirective,
  NgSelectComponent
} from '@ng-select/ng-select';
import {ObjetService} from '../../shared/services/objet.service';

@Component({
  selector: 'app-page-add-new-objet',
  imports: [
    ReactiveFormsModule,
    FormsModule,
    NgLabelTemplateDirective,
    NgOptionTemplateDirective,
    NgSelectComponent,
    NgOptionComponent,
  ],
  templateUrl: './page-add-new-objet.component.html',
  styleUrl: './page-add-new-objet.component.scss'
})
export class PageAddNewObjetComponent implements OnInit {

  faPlus = faPlus;
  faSave = faSave;
  faCheck = faCheck;

  private fb = inject(FormBuilder);

  categories: ICategorie[] = [];
  keywords: ICategorie[] = [];

  owners: IProprietaire[] = [];

  objectForm = this.fb.group({
    name: ['', Validators.required],
    description: [''],
    categories: [[] as ICategorie[]],
    keywords: [[] as ICategorie[]],
    owners: [[] as number[], Validators.required],
    imageMode: ['upload'],     // 'upload' | 'url'
    imageFile: [null as File | null],
    imageUrl: [''],
  });

  /** Champ intermédiaire du modal */
  newCategoryLabel = '';

  constructor(private categorieServices: CategorieService,
              private proprietaireServices: ProprietaireService,
              private objetServices: ObjetService
  ) {
  }

  ngOnInit(): void {

    this.categorieServices.getAllCategories(-1, -1).subscribe({
      next: (response) => {
        if (response.result) {
          this.categories = response.content.data.filter(cat => cat.Id_TyCategorie === 1);
          this.keywords = response.content.data.filter(cat => cat.Id_TyCategorie === 2);
        } else {
          console.error('Failed to fetch categories:', response.error);
        }
      },
      error: (error) => {
        console.error('Error fetching categories:', error);
      }
    });

    this.proprietaireServices.getAllProprietaires().subscribe({
      next: (response) => {
        console.log(response);
        if (response.result) {
          this.owners = response.content.data;
          // Set the first owner as default
          this.objectForm.patchValue({owners: [this.owners[0].Id_Proprietaire]});
        } else {
          console.error('Failed to fetch owners:', response);
        }
      },
      error: (error) => {
        console.error('Error fetching owners:', error);
      }
    });

  }


  onFileSelect(evt: Event): void {
    const file = (evt.target as HTMLInputElement).files?.[0] ?? null;
    this.objectForm.patchValue({imageFile: file});
  }

  addCategory(): void {
    const label = this.newCategoryLabel.trim();
    if (!label) return;

    const newCat : ICategorie = {Id_Categorie: -Date.now(), Nom : label, Id_TyCategorie : 1};

    this.categories.push(newCat);

    /* Pré-sélectionne la nouvelle catégorie */
    const current = this.objectForm.value.categories ?? [];
    this.objectForm.patchValue({categories: [...current, newCat]});

    this.newCategoryLabel = '';
    (bootstrap as any).Modal.getInstance(
      document.getElementById('addCategoryModal')!
    )?.hide();
  }

  addNewKeyword($event: any): void {


    if ('Id_Categorie' in $event && 'Nom' in $event) {
      return;
    }


    const label = $event.label.trim();
    if (!label) return;

    const isAlreadyExists = this.keywords.some(keyword => keyword.Nom.toLowerCase() === label.toLowerCase());
    if (isAlreadyExists) {
      console.debug('Keyword already exists:', label);
      return;
    }

    const newKeyword: ICategorie = {Id_Categorie: -Date.now(), Nom: label, Id_TyCategorie: 2};
    this.keywords.push(newKeyword);

    console.log('New keyword added:', $event, newKeyword);

    //const current = this.objectForm.value.keywords ?? [];
    //this.objectForm.patchValue({keywords: [...current, newKeyword]});
  }

  onSubmit(): void {
    if (this.objectForm.invalid) return;

    const payload = {...this.objectForm.value};

    const categoriesRaw: any[] | null | undefined = payload.categories;
    const categories: ICategorie[] = [];
    if (categoriesRaw) {
      categoriesRaw.forEach((cat: any) => {
        if ('Id_Categorie' in cat && 'Nom' in cat) {
          categories.push(cat as ICategorie);
        } else if ('label' in cat) {
          categories.push({Id_Categorie: -Date.now(), Nom: cat.label, Id_TyCategorie: 1});
        }
      });
    }

    const keywordsRaw : any[] | null | undefined = payload.keywords;
    const keywords: ICategorie[] = [];
    if (keywordsRaw) {
      keywordsRaw.forEach((kw: any) => {
        if ('Id_Categorie' in kw && 'Nom' in kw) {
          keywords.push(kw as ICategorie);
        } else if ('label' in kw) {
          keywords.push({Id_Categorie: -Date.now(), Nom: kw.label, Id_TyCategorie: 2});
        }
      });
    }


    console.log("KW:", keywords);

    this.objetServices.addNewObjet({
      nom: payload.name,
      description: payload.description,
      categories: categories,
      keywords: keywords,
      idProprietaire: payload.owners,
      imageMode: payload.imageMode,
      imageFile: payload.imageMode === 'upload' ? payload.imageFile : null,
      imageUrl: payload.imageMode === 'url' ? payload.imageUrl : ''

      }

    ).subscribe({
      next: (response) => {
        if (response.result) {
          console.log('Objet added successfully:', response.content);
          // Reset the form after successful submission
          this.objectForm.reset({
            imageMode: 'upload',
            owners: [this.owners[0].Id_Proprietaire],
          });
        } else {
          console.error('Failed to add objet:', response.error);
        }
      },
      error: (error) => {
        console.error('Error adding objet:', error);
      }
    });



  }
}
