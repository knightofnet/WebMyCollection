export interface IMedia {
  /*
  {"result":true,"content":{"type":"Objet[]","data":[{"Id_Objet":1,"Nom":"Pop Johnny Silverhand","Description":null,"DateAcquisition":null,"UrlAchat":"test","Proprietaire":[{"Id_Proprietaire":1,"Nom":"ArnaudL"}],"Media":[{"Id_Media":1,"Type":"DIRECT_LINK","UriServeur":"https:\/\/m.media-amazon.com\/images\/I\/71xzlUBfZCL.jpg","EstPrincipal":true,"Id_Objet":1}]},{"Id_Objet":2,"Nom":"Pop Freddy Mercury","Description":null,"DateAcquisition":null,"UrlAchat":null,"Proprietaire":[{"Id_Proprietaire":1,"Nom":"ArnaudL"}],"Media":[{"Id_Media":2,"Type":"DIRECT_LINK","UriServeur":"https:\/\/m.media-amazon.com\/images\/I\/719vAcCrJkL._AC_UF1000,1000_QL80_.jpg","EstPrincipal":true,"Id_Objet":2}]}]}}
   */

  Id_Media: number;
  Type: 'DIRECT_LINK_IMG' | 'FILE' | 'IMAGE' | 'VIDEO';
  UriServeur: string;
  EstPrincipal: boolean;
  Id_Objet: number;
}
