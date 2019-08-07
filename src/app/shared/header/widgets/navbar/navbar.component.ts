import { Component, OnInit } from '@angular/core';
import { MENUITEMS, Menu } from './navbar-items';
import { Router, ActivatedRoute } from "@angular/router";
import { CommonService } from '../../../../common.service';
declare var $: any;

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {
  
  public menuItems: Menu[];

  constructor(
    private __common: CommonService
  ) { }

  ngOnInit() {
    this.__common.allCategory().subscribe(
      (resp :any) => {
        //console.log(resp);
        //NOTE: resp is modified in common service
        if(resp.length > 0){
          this.menuItems = resp;
        }
      },
      (error) => { console.log(error) }
    );
  }

}
