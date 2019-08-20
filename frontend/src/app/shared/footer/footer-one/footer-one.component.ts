import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder,Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { CommonService } from '../../../common.service';
@Component({
  selector: 'app-footer-one',
  templateUrl: './footer-one.component.html',
  styleUrls: ['./footer-one.component.scss']
})
export class FooterOneComponent implements OnInit {
 subscriveForm: FormGroup;
 submitted = false;
  constructor(private fb: FormBuilder,private toastrService: ToastrService,private __common: CommonService) { 
  }

  ngOnInit(){
    this.subscriveForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }
  // convenience getter for easy access to form fields
  get f() { return this.subscriveForm.controls; }

  onClickSubmit(email) {
  	this.submitted = true;
  	if (this.subscriveForm.invalid) {
	    return;
	}
   if(email==''){
   		this.toastrService.error('Please enter the email to subscibe.');
   }

   this.__common.subscribeUser(email).subscribe(
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
