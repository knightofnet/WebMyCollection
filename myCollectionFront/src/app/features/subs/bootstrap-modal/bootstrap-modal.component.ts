import {Component, ElementRef, input, output, viewChild} from '@angular/core';
import * as bootstrap from 'bootstrap';

@Component({
  selector: 'app-bootstrap-modal',
  imports: [],
  templateUrl: './bootstrap-modal.component.html',
  styleUrl: './bootstrap-modal.component.scss'
})
export class BootstrapModalComponent {

  clickOk = output<void>();

  /**
   * Event emitted when the cancel button is clicked (true) or when the modal is closed (false).
   */
  clickCancel = output<boolean>();
  idModal = input.required<string>();

  modalRef = viewChild.required<ElementRef<HTMLDivElement>>('modalRef')

  body: string = '';
  title: string = '';

  btnOkClass: string = 'btn-primary';
  btnOkText: string = 'OK';
  btnCancelClass: string = 'btn-secondary';
  btnCancelText: string = 'Annuler';

  open() {


    const modal = new bootstrap.Modal(this.modalRef().nativeElement, {
      backdrop: 'static',
      keyboard: false,
      focus: true,
    });
    modal.show();

  }

  close() {
    const modal = bootstrap.Modal.getInstance(this.modalRef().nativeElement);
    if (modal) {
      modal.hide();
    }

  }
}
