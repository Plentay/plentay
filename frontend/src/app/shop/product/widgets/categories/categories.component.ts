import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router} from '@angular/router';
import { trigger, transition, style, animate } from "@angular/animations";
import { Product, ColorFilter, TagFilter } from '../../../../shared/classes/product';
import { ProductsService } from '../../../../shared/services/products.service';
import { CommonService } from '../../../../common.service';
import * as $ from 'jquery';

@Component({
  selector: 'app-categories',
  templateUrl: './categories.component.html',
  styleUrls: ['./categories.component.scss']
})
export class CategoriesComponent implements OnInit {

  public products     :   Product[] = [];
  public allItems     :   Product[] = [];
  public subCategories:[];
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private productsService: ProductsService,
    private __common: CommonService
  ) {
    this.route.params.subscribe(params => {
      this.__common.childCategory(12).subscribe(
        (resp:any) => {
          this.subCategories = resp;
        }
      );
    });
    /*
    this.route.params.subscribe(params => {
      const category = params['category'];
      this.productsService.getProductByCategory(category).subscribe(products => {

          this.allItems = products  // all products
          this.products = products.slice(0,8)
          console.log('dfgfdffffffff',this.products);

      // this.__common.subCategory(this.products[0].category_id).subscribe(
      //   (resp :any) => {
      //     //console.log(resp);
      //     //NOTE: resp is modified in common service
      //     if(resp.length > 0){
      //       this.subCategories = resp;
      //     }
      //   },
      //   (error) => { console.log(error) }
      // );  
          //this.getTags(products)
          //this.getColors(products)
      })
    });
    */
   }
   
  // collapse toggle
  ngOnInit() {
    $('.collapse-block-title').on('click', function(e) {
        e.preventDefault;
        var speed = 300;
        var thisItem = $(this).parent(),
          nextLevel = $(this).next('.collection-collapse-block-content');
        if (thisItem.hasClass('open')) {
          thisItem.removeClass('open');
          nextLevel.slideUp(speed);
        } else {
          thisItem.addClass('open');
          nextLevel.slideDown(speed);
        }
    });
  }

  // For mobile view
  public mobileFilterBack() {
     $('.collection-filter').css("left", "-365px");
  }

}
