import { Component, OnInit } from '@angular/core';
import { CommonService } from '../../common.service';

@Component({
  selector: 'app-terms-conditions',
  templateUrl: './terms-conditions.component.html',
  styleUrls: ['./terms-conditions.component.scss']
})
export class TermsConditionsComponent implements OnInit {
  content: '';
  constructor(
    private __common: CommonService
  ) { }

  ngOnInit() {
    this.__common.termsConditions().subscribe(
      (resp :any) => {
        if(resp.status == 1){
          this.content = resp.result.content;
        }
      },
      (error) => { console.log(error) }
    );
  }

}
