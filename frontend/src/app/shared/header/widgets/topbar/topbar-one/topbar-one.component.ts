import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Product } from '../../../../classes/product';
import { WishlistService } from '../../../../services/wishlist.service';
import { ProductsService } from '../../../../../shared/services/products.service';
import { Observable, of } from 'rxjs';
import { CommonService } from '../../../../../common.service';
//this.__common.isLoggedIn

@Component({
  selector: 'app-topbar',
  templateUrl: './topbar-one.component.html',
  styleUrls: ['./topbar-one.component.scss']
})
export class TopbarOneComponent implements OnInit {
  public searchResult:any;
  public hidden = false;
  public Noresshow= false;
  constructor(
    public productsService: ProductsService,
    private __common: CommonService,
    private __router: Router,
  ) { }

  ngOnInit() { }

  serchkeyword(event){
    if(event.target.value.length > 3){
      this.__common.search(event.target.value).subscribe(
      (resp :any) => {
          
          if(resp.status == 1){
            this.hidden = true; 
            this.Noresshow= false;
            this.searchResult = resp.result;
          }else{
            this.hidden = false;
            this.Noresshow = true;
            this.searchResult='';
          }
          
        },
        (error) => { console.log(error) }
      );
    }else{
      this.hidden = false;
      this.Noresshow = false;
    }
    
  }
  searchfocusOut(){
    this.hidden = false;
    this.Noresshow = false;
  }
  logout(){
    this.__common.logout();
    this.__router.navigate(['']);
  }

}
