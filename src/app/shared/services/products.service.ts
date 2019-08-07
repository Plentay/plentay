import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import { ToastrService } from 'ngx-toastr';
import { Product } from '../classes/product';
import { BehaviorSubject, Observable, of, Subscriber} from 'rxjs';
import { map, filter, scan } from 'rxjs/operators';
import 'rxjs/add/operator/map';
import { HttpClient } from '@angular/common/http';
import { AppGlobals } from '../../app.global';

// Get product from Localstorage
let products = JSON.parse(localStorage.getItem("compareItem")) || [];

@Injectable()

export class ProductsService {
  APIBASEURL: string;
  HEADERS: any;
  public currency : string = 'USD';
  public catalogMode : boolean = false;
  
  public compareProducts : BehaviorSubject<Product[]> = new BehaviorSubject([]);
  public observer   :  Subscriber<{}>;

  // Initialize 
  constructor(private http: Http,
    private __httpClient: HttpClient,
    private toastrService: ToastrService) { 
      this.APIBASEURL = AppGlobals.apiBaseUrl;
      this.HEADERS    = new Headers({'Content-Type': 'application/json', 'Access-Control-Allow-Origin' : '*' });
      this.compareProducts.subscribe(products => products = products);
  }

  // Observable Product Array
  /*
  private products(): Observable<Product[]> {
     return this.http.get('assets/data/products.json').map((res:any) => res.json())
  }
  */

  private products(): Observable<Product[]> {
    var url = this.APIBASEURL + 'api/products';
    var dataToPost = {
      'types': "",
    };
    return this.__httpClient.post(url, dataToPost, this.HEADERS).pipe(
      map(
        (resp:any) => {
          let products = [];
          if(resp.status == 1) {
            products = resp.result;
          }
          return products;
        }
      )
    );
    // return this.http.get('assets/data/products.json').map((res:any) => res.json())
  }

  // Get Products
  public getProducts(): Observable<Product[]> {
    return this.products();
  }

  // Get Products By Id
  public getProduct(id: number): Observable<any> {
    var url = this.APIBASEURL + 'api/productDetail';
    var dataToPost = {
      'product_id': id,
    };
    return this.__httpClient.post(url, dataToPost, this.HEADERS).pipe(
      map(
        (resp:any) => {
          let products = [];
          if(resp.status == 1) {
            products = resp.result[0];
          }
          return products;
        }
      )
    );
    //return this.products().pipe(map(products => { return products.find((products: Product) => { return products.id === id; }); }));
  }

   // Get Products By category
  public getProductByCategory(category: string): Observable<Product[]> {
    return this.products().pipe(map(items => 
       items.filter((item: Product) => {
         if(category == 'all')
            return item
         else
            return item.category === category; 
        
       })
     ));
  }
  
   /*
      ---------------------------------------------
      ----------  Compare Product  ----------------
      ---------------------------------------------
   */

  // Get Compare Products
  public getComapreProducts(): Observable<Product[]> {
    const itemsStream = new Observable(observer => {
      observer.next(products);
      observer.complete();
    });
    return <Observable<Product[]>>itemsStream;
  }

  // If item is aleready added In compare
  public hasProduct(product: Product): boolean {
    const item = products.find(item => item.id === product.id);
    return item !== undefined;
  }

  // Add to compare
  public addToCompare(product: Product): Product | boolean {
    var item: Product | boolean = false;
    if (this.hasProduct(product)) {
      item = products.filter(item => item.id === product.id)[0];
      const index = products.indexOf(item);
    } else {
      if(products.length < 4)
        products.push(product);
      else 
        this.toastrService.warning('Maximum 4 products are in compare.'); // toasr services
    }
      localStorage.setItem("compareItem", JSON.stringify(products));
      return item;
  }

  // Removed Product
  public removeFromCompare(product: Product) {
    if (product === undefined) { return; }
    const index = products.indexOf(product);
    products.splice(index, 1);
    localStorage.setItem("compareItem", JSON.stringify(products));
  }
   
}