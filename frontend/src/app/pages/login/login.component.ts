import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { CommonService } from '../../common.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  showValidationError: boolean = false;
  validationErrorHtml = '';
  loginTrue:boolean = false;

  constructor(
    private __common: CommonService,
    private __router: Router,
    private formBulder: FormBuilder,
  ) { 
    this.loginForm = formBulder.group({
      // define your control in you form
      email: ['', [
          Validators.required,
          Validators.email
        ]
      ],
      password: ['', [
          Validators.required
        ]
      ]
    });
  }

  ngOnInit() {
  }

  onLogin(){
    const form = this.loginForm;
    const email = form.value.email;
    const password = form.value.password;
    this.__common.login(email, password)
    .subscribe(
      (resp :any) => {
          const response = resp;
          if(response.status == 1){
            this.loginTrue = true;
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
