import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { CommonService } from '../../common.service';

@Component({
  selector: 'app-contact',
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.scss']
})
export class ContactComponent implements OnInit {
  contactForm: FormGroup;
  showValidationError: boolean = false;
  validationErrorHtml = '';
  successHtml = '';
  contactTrue:boolean = false;

  constructor(
    private __common: CommonService,
    private formBulder: FormBuilder,
  ) {
    this.contactForm = formBulder.group({
      // define your control in you form
      name: ['', [
          Validators.required,
          Validators.minLength(2)
        ]
      ],
      mobile_number: ['', [
          Validators.required,
          Validators.minLength(10),
          Validators.maxLength(15)
        ]
      ],
      subject: ['', [
          Validators.required,
          Validators.minLength(10)
        ]
      ],
      message: ['', [
          Validators.required,
          Validators.minLength(10)
        ]
      ]
    });
   }

  ngOnInit() {
  }

  onContact(){
    const form = this.contactForm;
    const name = form.value.name;
    const mobile_number = form.value.mobile_number;
    const subject = form.value.subject;
    const message = form.value.message;
    this.__common.contactUs(name, mobile_number, subject, message)
    .subscribe(
      (resp :any) => {
          const response = resp;
          if(response.status == 1){
            this.contactTrue = true;
            this.successHtml = response.message;
          } else {
            this.showValidationError = true;
            this.validationErrorHtml = '<div class="alert alert-danger mt-3">' + response.message + '</div>';
          }
      },
      (error :any) => { console.log(error); },
      () => {}
    );
  }

}
