// import { Modal } from 'bootstrap';
// import { Controller } from 'stimulus';
//
// export default class extends Controller {
//     static targets = ['modal', 'modalBody'];
//     static values = {
//         url: String,
//     };
//     modal = null;
//
//     async openModal(event) {
//         event.preventDefault();
//
//         this.modal = new Modal(this.modalTarget);
//         this.modal.show();
//
//         const response = await fetch(this.urlValue);
//         this.modalBodyTarget.innerHTML = await response.text();
//     }
// }
