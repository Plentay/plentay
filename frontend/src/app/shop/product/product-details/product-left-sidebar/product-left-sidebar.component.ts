import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router} from '@angular/router';
import { FormGroup, FormBuilder, Validators, NgForm } from '@angular/forms';
import { Product } from '../../../../shared/classes/product';
import { ProductsService } from '../../../../shared/services/products.service';
import { WishlistService } from '../../../../shared/services/wishlist.service';
import { CartService } from '../../../../shared/services/cart.service';
import { Observable, of } from 'rxjs';
import * as $ from 'jquery';

@Component({
  selector: 'app-product-left-sidebar',
  templateUrl: './product-left-sidebar.component.html',
  styleUrls: ['./product-left-sidebar.component.scss']
})
export class ProductLeftSidebarComponent implements OnInit {

  private product_id: number
  public product            :   Product = {};
  public products           :   Product[] = [];
  public counter            :   number = 1; 
  public selectedSize       :   any = '';
  public iStyle               = "width:100%; height:100%;"

  reviewForm: FormGroup;
  fullName = /^[ A-Za-z0-9_@./#&+\-=!%*(\){}[\]?\$]*$/;
  emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  showValidationError: boolean = false;
  validationErrorHtml = '';
  reviewTrue:boolean = false;

  //Get Product By Id
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    public productsService: ProductsService,
    private wishlistService: WishlistService,
    private cartService: CartService,
    private formBulder: FormBuilder,
  ) {
      this.route.params.subscribe(params => {
        const id = +params['id'];
        this.product_id = id;
        this.productsService.getProduct(id).subscribe(product => this.product = product)
      });

      this.reviewForm = formBulder.group({
        // define your control in you form
        name: ['', [
            Validators.required,
            Validators.minLength(2),
            Validators.maxLength(100),
            Validators.pattern(this.fullName)
          ]
        ],
        email: ['', [
            Validators.required,
            Validators.email,
            Validators.maxLength(100),
            Validators.pattern(this.emailRegex)
          ]
        ],
        subject: ['', [
            Validators.required,
            Validators.minLength(2),
            Validators.maxLength(50)
          ]
        ],
        message: ['', [
            Validators.required,
            Validators.minLength(2),
            Validators.maxLength(500)
          ]
        ],
        
      });
  }

  ngOnInit() {
    this.productsService.getProducts().subscribe(product => this.products = product);
  }
  
  onReview(){
    const form = this.reviewForm;
    const product_id = this.product_id;
    const user_id = 0;
    const rating = 3;
    const name = form.value.name;
    const email = form.value.email;
    const subject = form.value.subject;
    const message = form.value.message;
    
    this.productsService.writeReview(product_id, user_id, rating, name, email, subject, message)
    .subscribe(
      (resp :any) => {
          const response = resp;
          if(resp.status == 1){
            this.reviewTrue = true;
          }
      },
      (error :any) => { console.log(error); },
      () => {}
    );
  }
  
  // product zoom 
  onMouseOver(): void {
    document.getElementById("p-zoom").classList.add('product-zoom');
  }

  onMouseOut(): void {
    document.getElementById("p-zoom").classList.remove('product-zoom');
  }

  public slideConfig = {
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    fade: true,
  };

  public slideNavConfig = {
    vertical: false,
    slidesToShow: 3,
    slidesToScroll: 1,
    asNavFor: '.product-slick',
    arrows: false,
    dots: false,
    focusOnSelect: true
  }

  public increment() { 
      this.counter += 1;
  }

  public decrement() {
      if(this.counter >1){
         this.counter -= 1;
      }
  }

  // For mobile filter view
  public mobileSidebar() {
    $('.collection-filter').css("left", "-15px");
  }

  // Add to cart
  public addToCart(product: Product, quantity) {
    if (quantity == 0) return false;
    this.cartService.addToCart(product, parseInt(quantity));
  }

  // Add to cart
  public buyNow(product: Product, quantity) {
     if (quantity > 0) 
       this.cartService.addToCart(product,parseInt(quantity));
       this.router.navigate(['/home/checkout']);
  }

  // Add to wishlist
  public addToWishlist(product: Product) {
     this.wishlistService.addToWishlist(product);
  }

  
  // Change size variant
  public changeSizeVariant(variant) {
     this.selectedSize = variant;
  }

}
