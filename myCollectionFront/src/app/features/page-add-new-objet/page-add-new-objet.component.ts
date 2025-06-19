import {Component, ElementRef, inject, OnInit, viewChild} from '@angular/core';
import {FormBuilder, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import {faCheck, faPlus, faSave} from '@fortawesome/free-solid-svg-icons';
import {ICategorie} from '../../shared/interfaces/i-categorie';
import {IProprietaire} from '../../shared/interfaces/i-proprietaire';
import {CategorieService} from '../../shared/services/categorie.service';
import {ProprietaireService} from '../../shared/services/proprietaire.service';
import * as bootstrap from 'bootstrap';
import {NgOptionComponent, NgSelectComponent} from '@ng-select/ng-select';
import {ObjetService} from '../../shared/services/objet.service';
import {ActivatedRoute} from '@angular/router';
import {forkJoin} from 'rxjs';
import {IObjet} from '../../shared/interfaces/i-objet';
import {IParamForCreateOrUpdateObjet} from '../../shared/interfaces/side/i-param-for-create-or-update-objet';

@Component({
  selector: 'app-page-add-new-objet',
  imports: [
    ReactiveFormsModule,
    FormsModule,
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
  private readonly allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  private readonly maxSize = 5 * 1024 * 1024; // 5M


  isDragOver = false;


  categories: ICategorie[] = [];
  keywords: ICategorie[] = [];

  owners: IProprietaire[] = [];

  currentObjetEdited: IObjet | null = null;

  objectForm = this.fb.group({
    name: ['', Validators.required],
    description: [''],
    categories: [[] as number[]],
    keywords: [[] as number[]],
    owners: [[] as number[], Validators.required],
    imageMode: ['upload'],     // 'upload' | 'url'
    imageFile: [null as File | null],
    imageUrl: [''],
  });

  inputFile = viewChild.required<ElementRef<HTMLInputElement>>('inputFile');

  pendingUploads: File[] = [];


  /** Champ intermédiaire du modal */
  newCategoryLabel = '';

  constructor(private categorieServices: CategorieService,
              private proprietaireServices: ProprietaireService,
              private objetServices: ObjetService,
              private route: ActivatedRoute
  ) {
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');

    const categories$ = this.categorieServices.getAllCategories(-1, -1);
    const proprietaires$ = this.proprietaireServices.getAllProprietaires();

    forkJoin([categories$, proprietaires$]).subscribe({
      next: ([catResponse, propResponse]) => {
        if (catResponse.result) {
          this.categories = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 1);
          this.keywords = catResponse.content.data.filter(cat => cat.Id_TyCategorie === 2);
        } else {
          console.error('Failed to fetch categories:', catResponse.error);
        }
        if (propResponse.result) {
          this.owners = propResponse.content.data;
          this.objectForm.patchValue({owners: [this.owners[0].Id_Proprietaire]});
        } else {
          console.error('Failed to fetch owners:', propResponse);
        }

        if (id) {
          this.asyncGetObjetFromRemote(id);
        }
      },
      error: (error) => {
        console.error('Error fetching categories or owners:', error);
      }
    });
  }


  private asyncGetObjetFromRemote = (id: string) => {
    this.objetServices.getObjetById(+id).subscribe({
      next: (response) => {
        if (response.result) {

          // Pré-remplir le formulaire avec les données de l'objet récupéré
          const objet = response.content.data;

          this.currentObjetEdited = objet;

          this.objectForm.patchValue({
            name: objet.Nom,
            description: objet.Description,
            categories: objet.Categorie?.map(cat => cat.Id_Categorie),
            keywords: objet.Keyword?.map(cat => cat.Id_Categorie),
            owners: objet.Proprietaire.map(owner => owner.Id_Proprietaire),
            imageUrl : ''
            //imageMode: objet ? 'url' : 'upload',
            //imageUrl: objet.imageUrl || '',
            //imageFile: null
          });
        } else {
          console.error('Failed to fetch objet:', response.error);
        }
      },
      error: (error) => {
        console.error('Error fetching objet:', error);
      }
    });
  }

  onFileSelect(evt: Event): void {
    const file = (evt.target as HTMLInputElement).files?.[0] ?? null;
    this.updateFormWithFile(file);
  }


  addCategory(): void {
    const label = this.newCategoryLabel.trim();
    if (!label) return;

    console.log('Adding new category:', label);

    const newCat: ICategorie = {Id_Categorie: -Date.now(), Nom: label, Id_TyCategorie: 1};

    this.categories.push(newCat);
    this.categories = [...this.categories];


    /* Pré-sélectionne la nouvelle catégorie */
    const current = this.objectForm.value.categories ?? [];
    this.objectForm.patchValue({categories: [...current, newCat.Id_Categorie]});

    this.newCategoryLabel = '';
    (bootstrap as any).Modal.getInstance(
      document.getElementById('addCategoryModal')!
    )?.hide();
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

  onSubmit(): void {
    if (this.objectForm.invalid) return;

    const payload = {...this.objectForm.value};

    const categoriesId: number[] | null | undefined = payload.categories;
    const categories: ICategorie[] = [];

    if (categoriesId) {
      categoriesId.forEach((cat: any) => {

        if (cat < 0) {
          // If the category ID is negative, it means it's a new category
          const newCat: ICategorie = {Id_Categorie: cat, Nom: this.newCategoryLabel, Id_TyCategorie: 1};
          categories.push(newCat);
        } else {
          // If the category ID is positive, it means it's an existing category
          const existingCat = this.categories.find(c => c.Id_Categorie === cat);
          if (existingCat) {
            categories.push(existingCat);
          } else {
            console.warn(`Category with ID ${cat} not found.`);
          }
        }


      });
    }

    const keywordsRaw: number[] | null | undefined = payload.keywords;
    const keywords: ICategorie[] = [];
    if (keywordsRaw) {
      keywordsRaw.forEach((kw: any) => {
        if (kw < 0) {
          // If the category ID is negative, it means it's a new category
          const newCat: ICategorie = {Id_Categorie: kw, Nom: this.newCategoryLabel, Id_TyCategorie: 1};
          keywords.push(newCat);
        } else {
          // If the category ID is positive, it means it's an existing category
          const existingCat = this.keywords.find(c => c.Id_Categorie === kw);
          if (existingCat) {
            keywords.push(existingCat);
          } else {
            console.warn(`Category (keyword) with ID ${kw} not found.`);
          }
        }
      });
    }


    console.log("KWD:", keywords);
    console.log("CAT:", categories);

    const uploadProps: IParamForCreateOrUpdateObjet = {
      nom: payload.name,
      description: payload.description,
      categories: categories,
      keywords: keywords,
      idProprietaire: payload.owners,
      imageMode: payload.imageMode,
      imageFile: payload.imageMode === 'upload' ? payload.imageFile : null,
      imageUrl: payload.imageMode === 'url' ? payload.imageUrl : ''

    }

    console.log('Submitting objet with payload:', uploadProps);

    let subscribeAddOrUpdate$ = null;
    let afterSubmit = () => {
    };
    if (this.currentObjetEdited) {
      subscribeAddOrUpdate$ = this.objetServices.updateObjet(this.currentObjetEdited.Id_Objet, uploadProps);
      afterSubmit = () => {
        this.asyncGetObjetFromRemote(this.currentObjetEdited!.Id_Objet.toString());
      }
    } else {
      subscribeAddOrUpdate$ = this.objetServices.addNewObjet(uploadProps);
      afterSubmit = () => {
        this.objectForm.reset({
          imageMode: 'upload',
          owners: [this.owners[0].Id_Proprietaire],
        });
      }
    }

    if (subscribeAddOrUpdate$) {
      subscribeAddOrUpdate$.subscribe({
        next: (response) => {
          if (response.result) {
            console.log('Objet added successfully:', response.content);
            // Reset the form after successful submission
            afterSubmit();
            this.currentObjetEdited = null;
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

  addMediaForCurrentObjet() {

    if (
      this.objectForm.value.imageMode
      && (
        this.objectForm.value.imageMode === 'url' &&  this.objectForm.controls.imageUrl.invalid
        || this.objectForm.value.imageMode === 'upload' && !this.objectForm.value.imageFile
      )) {

      console.error('Invalid form data. Please check the image mode and file/url input.');
      return;
    }

    const payload = {
      imageMode: this.objectForm.value.imageMode!,
      imageFile: this.objectForm.value.imageMode === 'upload' ? this.objectForm.value.imageFile! : null,
      imageUrl: this.objectForm.value.imageMode === 'url' ? this.objectForm.value.imageUrl! : '',
      idObjet: this.currentObjetEdited ? this.currentObjetEdited.Id_Objet : null
    };

    console.log('Submitting objet with payload:', payload);

    this.objetServices.addMediaForObjet(payload).subscribe({
      next : (response) => {
        if (response.result) {
          console.log('Media added successfully:', response.content);

          this.currentObjetEdited?.Media?.push(response.content.data);

        }
        else {
          console.error('Failed to add media:', response.error);
        }
      }
      , error: (error) => {
        console.error('Error adding media:', error);
      }
    });

  }

  removeMedia(idMedia: number) {
      this.objetServices.deleteMediaForObjet(idMedia).subscribe({
        next: (response) => {
          if (response.result) {
            console.log('Media removed successfully:', response.content);
            if (this.currentObjetEdited) {
              this.currentObjetEdited.Media = this.currentObjetEdited.Media?.filter(media => media.Id_Media !== idMedia);
            }
          } else {
            console.error('Failed to remove media:', response.error);
          }
        },
        error: (error) => {
          console.error('Error removing media:', error);
        }
      });
  }

  onFileDragOver($event: DragEvent) {
    $event.preventDefault();
    this.isDragOver = true;
  }

  onFileDragLeave($event: DragEvent) {
    $event.preventDefault();
    this.isDragOver = false;
  }


  onFileDrop($event: DragEvent) {
    $event.preventDefault();
    this.isDragOver = false;

    try {
      const file = $event.dataTransfer?.files[0]
      if (file) {
        this.validateFile(file);
        console.log('File dropped:', file);

        this.inputFile().nativeElement.files = $event.dataTransfer?.files;
        this.updateFormWithFile(file);

        //updateDropZoneText()
      }
    } catch (error) {
      //showError(error.message)
      console.error('Error during file drop:', error);
    }
  }


  validateFile(file: File) {
    if (!this.allowedTypes.includes(file.type)) {
      throw new Error('Invalid file type. Please upload JPEG, PNG or PDF files.')
    }
    if (file.size > this.maxSize) {
      throw new Error('File too large. Maximum size is 5MB.')
    }
  }


  private updateFormWithFile = (file: File | null) => {
    this.objectForm.patchValue({imageFile: file});

    if (file) {
      this.pendingUploads.push(file);
      this.pendingUploads = [...this.pendingUploads]; // Trigger change detection
    }
  }


}
