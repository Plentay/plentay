import { Component, OnInit } from '@angular/core';
import { CommonService } from '../../../../common.service';

@Component({
  selector: 'app-categories',
  templateUrl: './categories.component.html',
  styleUrls: ['./categories.component.scss']
})
export class CategoriesComponent implements OnInit {
  public categoryItems: [];
  constructor(
    private __common: CommonService
  ) { }

  ngOnInit() {
    this.__common.allCategory().subscribe(
      (resp :any) => {
        //console.log(resp);
        //NOTE: resp is modified in common service
        if(resp.length > 0){
          this.categoryItems = resp;
        }
      },
      (error) => { console.log(error) }
    );
  }

}
