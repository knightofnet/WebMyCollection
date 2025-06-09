import {IProprietaire} from './i-proprietaire';
import {IMedia} from './i-media';
import {ICategorie} from './i-categorie';

export interface IObjet {
  Id_Objet: number
  Nom: string
  Description?: string | null
  DateAcquisition?: Date | null
  UrlAchat?: string | null,
  DateAjout : Date | null,
  Proprietaire: IProprietaire[]
  Media? : IMedia[] | null
  Categorie? : ICategorie[] | null
  Keyword? : ICategorie[] | null


}
