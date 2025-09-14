import {computed, effect, Injectable, signal} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ImageStoreService {
  private _blob = signal<Blob | null>(null);
  readonly blob = computed(() => this._blob());

  private _objectUrl = signal<string | null>(null);
  readonly objectUrl = computed(() => this._objectUrl()); // pour le template si besoin

  constructor() {
    effect((onCleanup) => {
      const b = this._blob(); // seule dépendance

      // Par défaut, pas d’URL tant qu’on n’a pas de blob
      this._objectUrl.set(null);

      if (!b) return;

      const url = URL.createObjectURL(b);
      this._objectUrl.set(url);

      // Nettoyage: révoquer l'URL quand le blob change ou à la destruction
      onCleanup(() => {
        URL.revokeObjectURL(url);
        this._objectUrl.set(null);
      });
    });
  }

  setBlob(b: Blob | File) {
    this._blob.set(b);
  }

  clear() {
    this._blob.set(null);
  }

  // Si tu préfères une API "fonction" dans le TS de tes composants :
  objectUrlValue() {
    return this._objectUrl();
  }
}
