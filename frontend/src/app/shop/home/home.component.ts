import { Component, OnInit } from '@angular/core';
import { Product } from '../../shared/classes/product';
import { ProductsService } from '../../shared/services/products.service';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  
   public products: Product[] = [];
  
  constructor(private productsService: ProductsService) {   }

  ngOnInit() {
    /* 
    this.productsService.getProducts().subscribe(product => { 
      console.log(product);
      product.filter((item: Product) => {
         if(item.category == 'electronics')
          this.products.push(item)
      })
    });
    */
   this.productsService.getProducts().subscribe(product => { 
     //console.log(product);
      product.filter((item: Product) => {
          this.products.push(item)
      })
    });
  }

}
