import {Component, computed, signal} from '@angular/core';
import {PhotoPayloadComponent} from '../subs/photo-payload/photo-payload.component';
import {ReactiveFormsModule} from '@angular/forms';
import {ImageStoreService} from '../../shared/services/image-store.service';

@Component({
  selector: 'app-page-quick-add-new',
  imports: [
    PhotoPayloadComponent,
    ReactiveFormsModule
  ],
  templateUrl: './page-quick-add-new.component.html',
  styleUrl: './page-quick-add-new.component.scss'
})
export class PageQuickAddNewComponent {

  photoSelected: any;

  currentZone = signal(0);
  zones: string[] = ['zone-photos', 'zone-preview-photos', 'zone-nom', 'zone-details', 'zone-final'];


  public constructor(private imageStoreService: ImageStoreService) {
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

    }


  }
}
