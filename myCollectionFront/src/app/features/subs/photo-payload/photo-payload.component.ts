import {Component, ElementRef, OnDestroy, output, viewChild} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {IPhotoPayload, PhotoSource} from '../../../shared/interfaces/subs/i-photo-payload';
import {ImageStoreService} from '../../../shared/services/image-store.service';


@Component({
  selector: 'app-photo-payload',
  imports: [FormsModule],
  styleUrls: ['./photo-payload.component.scss'],
  templateUrl: './photo-payload.component.html'
})
export class PhotoPayloadComponent implements OnDestroy {


  //@Output() photoSelected = new EventEmitter<IPhotoPayload>();

  photoSelected = output<IPhotoPayload>();

  videoRef = viewChild.required<ElementRef<HTMLVideoElement>>('video');
  canvasRef = viewChild.required<ElementRef<HTMLCanvasElement>>('canvas');

  // Onglets rendus avec @for
  readonly tabs = [

    {id: 'upload', label: 'Importer', icon: '🖼️'},
    //{id: 'camera', label: 'Appareil photo', icon: 'fa-solid fa-camera-retro'},
    {id: 'url', label: 'Depuis une URL', icon: '🔗'},
  ] as const;

  mode: PhotoSource = 'upload';
  cameraActive = false;
  mediaStream?: MediaStream;

  previewSrc: string | null = null;
  lastObjectUrl: string | null = null;
  error: string | null = null;

  imageUrl = '';

  // paramètres de sortie/traitement
  maxWidth = 1600;
  maxHeight = 1600;
  quality = 0.92; // JPEG

  public constructor(private imageStoreService: ImageStoreService) {

  }

  switchMode(m: PhotoSource) {
    this.mode = m;
    this.error = null;

  }


  async onFileChange(ev: Event) {
    this.error = null;
    const input = ev.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      this.error = 'Fichier non image.';
      return;
    }

    console.log('Selected file:', file);
    const processed = await this.resizeIfNeeded(file, this.maxWidth, this.maxHeight, this.quality);
    console.log("processed");
    await this.imageStoreService.setBlob(processed);

    this.photoSelected.emit(
      {
        source: 'upload',
        imgName : file.name,
        url : null,
        mimeType : file.type
      }
    );

    //await this.processBlob(file, file.name || 'photo-upload.jpg', 'upload');
    //input.value = '';
  }

  async loadFromUrl() {
    this.error = null;
    try {
      const url = this.imageUrl?.trim();
      if (!url) {
        this.error = 'URL vide.';
        return;
      }
      let parsed: URL;
      try {
        parsed = new URL(url);
      } catch {
        this.error = 'URL invalide.';
        return;
      }
      const resp = await fetch(parsed.toString(), {mode: 'cors'});
      if (!resp.ok) {
        this.error = `Échec du chargement (${resp.status}).`;
        return;
      }
      const blob = await resp.blob();
      if (!blob.type.startsWith('image/')) {
        this.error = 'Le contenu téléchargé n’est pas une image.';
        return;
      }
      const fileName = this.deriveFileNameFromUrl(parsed) ?? 'photo-url.jpg';
      await this.processBlob(blob, fileName, 'url');
    } catch (e: any) {
      this.error = 'Erreur au chargement de l’URL : ' + (e?.message ?? e);
    }
  }

  private deriveFileNameFromUrl(u: URL): string | null {
    const p = u.pathname.split('/').pop() || '';
    if (!p) return null;
    return decodeURIComponent(p);
  }

  private async processBlob(blob: Blob, fileName: string, source: PhotoSource) {
    /*

        const processed = await this.resizeIfNeeded(blob, this.maxWidth, this.maxHeight, this.quality);
        if (this.lastObjectUrl) URL.revokeObjectURL(this.lastObjectUrl);
        const objectUrl = URL.createObjectURL(processed);
        this.lastObjectUrl = objectUrl;
        this.previewSrc = objectUrl;

        this.photoSelected.emit({
          blob: processed,
          src: objectUrl,
          fileName,
          source
        });

     */
  }

  private async resizeIfNeeded(blob: Blob, maxW: number, maxH: number, quality: number): Promise<Blob> {
    const bitmap = await createImageBitmap(blob).catch(() => null);
    if (!bitmap) return blob;

    const {width, height} = bitmap;
    const ratio = Math.min(1, maxW / width, maxH / height);
    if (ratio >= 1) {
      bitmap.close?.();
      return blob;
    }

    const targetW = Math.round(width * ratio);
    const targetH = Math.round(height * ratio);

    const canvas = document.createElement('canvas');
    canvas.width = targetW;
    canvas.height = targetH;
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      bitmap.close?.();
      return blob;
    }
    ctx.drawImage(bitmap, 0, 0, targetW, targetH);
    bitmap.close?.();

    return await new Promise<Blob>((resolve) =>
      canvas.toBlob(b => resolve(b as Blob), 'image/jpeg', quality)
    );
  }

  ngOnDestroy(): void {

    if (this.lastObjectUrl) URL.revokeObjectURL(this.lastObjectUrl);
  }


}
