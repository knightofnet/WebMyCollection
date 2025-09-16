export type PhotoSource = 'camera' | 'upload' | 'url';


export interface IPhotoPayload {

  source: PhotoSource;
  imgName: string | null;
  url: string | null;
  mimeType : string | null;

}
