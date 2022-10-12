// import { Controller } from 'stimulus';
// import { useDispatch } from 'stimulus-use';
//
// export default class extends Controller {
//     static values = {
//         title: String,
//         text: String,
//         icon: String,
//         confirmButtonText: String,
//         submitAsync: Boolean,
//     }
//
//     connect() {
//         useDispatch(this, { debug: true });
//     }
//
//     async onSubmit(event) {
//         event.preventDefault();
//
//         const { default: Swal } = await import('sweetalert2');
//         Swal.fire({
//             title: this.titleValue || null,
//             text: this.textValue || null,
//             icon: this.iconValue || null,
//             showCancelButton: true,
//             showDenyButton: false,
//             confirmButtonText: this.confirmButtonTextValue || 'Yes',
//             showLoaderOnConfirm: true,
//             preConfirm: () => {
//                 return this.submitForm();
//             },
//             buttonsStyling: false,
//             customClass: {
//                 confirmButton: 'btn btn-primary m-2',
//                 denyButton: 'btn btn-danger m-2',
//                 cancelButton: 'btn btn-secondary m-2'
//             }
//         });
//     }
//
//     async submitForm() {
//         if (!this.submitAsyncValue) {
//             this.element.submit();
//
//             return;
//         }
//
//         let url = this.element.action;
//         let init = {method: this.element.method};
//
//         if (['get', 'head'].includes(this.element.method)) {
//             url += '?' + new URLSearchParams(new FormData(this.element)).toString();
//         } else {
//             init.body = new URLSearchParams(new FormData(this.element));
//         }
//
//         const response = await fetch(url, init);
//
//         this.dispatch('async:submitted', {
//             response,
//         });
//     }
// }
