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
  
  constructor(
    public productsService: ProductsService,
    private __common: CommonService,
    private __router: Router,
  ) { }

  ngOnInit() { }

  logout(){
    this.__common.logout();
    this.__router.navigate(['']);
  }

}
