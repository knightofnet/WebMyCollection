import {ICategorie} from '../i-categorie';

export interface IParamForCreateOrUpdateObjet {
  nom: string | null | undefined;
  description: string | null | undefined;
  categories: ICategorie[] | null | undefined;
  keywords: ICategorie[] | null | undefined;
  idProprietaire: number[] | null | undefined;
  imageMode: string | null | undefined;
  imageFile: File | null | undefined;
  imageUrl: string | null | undefined
}
