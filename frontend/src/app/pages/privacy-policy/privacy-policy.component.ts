import { Component, OnInit } from '@angular/core';
import { CommonService } from '../../common.service';

@Component({
  selector: 'app-privacy-policy',
  templateUrl: './privacy-policy.component.html',
  styleUrls: ['./privacy-policy.component.scss']
})
export class PrivacyPolicyComponent implements OnInit {
  content: '';
  constructor(
    private __common: CommonService
  ) { }

  ngOnInit() {
    this.__common.privacyPolicy().subscribe(
      (resp :any) => {
        if(resp.status == 1){
          this.content = resp.result.content;
        }
      },
      (error) => { console.log(error) }
    );
  }

}
