import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder,Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { CommonService } from '../../common.service';
@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss']
})
export class RegisterComponent implements OnInit {
 registrationForm: FormGroup;
 submitted = false;
  constructor(private fb: FormBuilder,private toastrService: ToastrService,private __common: CommonService) { }

  ngOnInit() {
  	this.registrationForm = this.fb.group({
  		username: 	['', [Validators.required]],
  		email: 		['', [Validators.required, Validators.email]],
  		password: 	['', [Validators.required]],
  		contactname: ['', [Validators.required]],
  		address: ['', [Validators.required]],
  		url: ['', [Validators.required]],
  		phone: ['', [Validators.required]],
      	about: ['', [Validators.required]]
    });
  }

   // convenience getter for easy access to form fields
  get f() { return this.registrationForm.controls; }


 onClickSubmit(registerform) {
 console.log(registerform);
  	this.submitted = true;
  	if (this.registrationForm.invalid) {
	    return;
	}
   
   this.__common.companyRegistration(registerform).subscribe(
      (resp :any) => {
      	console.log(resp);
        if(resp.status == 1){
			this.toastrService.success(resp.message);
        }else{
        	this.toastrService.error(resp.message);
        }
        
      },
      (error) => { console.log(error) }
    );




  }

}
