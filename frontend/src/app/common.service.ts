import { Injectable } from '@angular/core';
import { map } from 'rxjs/operators';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AppGlobals } from './app.global';
declare var jQuery:any;

@Injectable({
  providedIn: 'root'
})
export class CommonService {
  APIBASEURL: string; 
  HEADERS:any;
  constructor(
    private __httpClient: HttpClient,
    private __router: Router
  ) { 
    this.HEADERS    = new Headers({'Content-Type': 'application/json', 'Access-Control-Allow-Origin' : '*' });
    this.APIBASEURL = AppGlobals.apiBaseUrl;
  }

  aboutUs(){
    var url = this.APIBASEURL + 'api/about-us';
    return this.__httpClient.get( url );
  }

  privacyPolicy(){
    var url = this.APIBASEURL + 'api/privacy-policy';
    return this.__httpClient.get( url );
  }

  termsConditions(){
    var url = this.APIBASEURL + 'api/terms-conditions';
    return this.__httpClient.get( url );
  }

  contactUs(name:string, mobile_number:string, subject:string, message:string){
    const url = this.APIBASEURL + 'api/contact-us';
    let dataToPost = { name, mobile_number, subject, message };
    return this.__httpClient.post( url, dataToPost, this.HEADERS );
  }

  allCategory(){
    var url = this.APIBASEURL + 'api/allCategory';
    return this.__httpClient.get( url ).pipe(
      map(
        (resp:any) => {
          let menuItems = [];
          if(resp.status == 1) {
            let result = resp.result;
            for(let i =0; i<=5; i++) {
              let item = {
                title: result[i].parent.name, type: 'link', path: '/home/left-sidebar/collection/'+result[i].parent.slug,
              };
              menuItems.push(item);
            }
          }
          return menuItems;
        }
      )
    );
  }

  brands(){
    var url = this.APIBASEURL + 'api/brands';
    return this.__httpClient.get( url ).pipe(
      map(
        (resp:any) => {
          let logo = [];
          if(resp.status == 1){
            let row:any;
            for(row of resp.result){
              row.image = this.APIBASEURL+row.image;
              logo.push(row);
            }
            return logo;
          } 
        }
      )
    );
  }

  
  subCategory(id:number){
    var url = this.APIBASEURL + '/api/childCategory';
    var dataToPost = {
      'category_id': id,
    };
    return this.__httpClient.post(url, dataToPost, this.HEADERS);
  }

  subscribeUser(email:string){
    var url = this.APIBASEURL + '/api/newsletter';
    var dataToPost = {
      'email': email,
    };
    return this.__httpClient.post(url, dataToPost, this.HEADERS);
  }

  companyRegistration(registrationdata){
    var url = this.APIBASEURL + '/api/company-registration';
    var dataToPost = {
      'name'        : registrationdata.username,
      'email'       : registrationdata.email,
      'password'    : registrationdata.password,
      'contactname' : registrationdata.contactname,
      'address'     : registrationdata.address,
      'url'         : registrationdata.url,
      'phone'       : registrationdata.phone,
      'about'       : registrationdata.about
    };
    return this.__httpClient.post(url, dataToPost, this.HEADERS);
  }

  childCategory(id:number){
    const url = this.APIBASEURL + 'api/childCategory';
    let dataToPost = {
      'category_id': id,
    };
    return this.__httpClient.post( url, dataToPost, this.HEADERS ).pipe(
      map(
        (resp:any) => {
          let subcats = [];
          if(resp.status == 1) {
            subcats = resp.result[0].sub_category;
          }
          return subcats;
        }
      )
    );
  }

  isAuthenticated(){
    const token = (sessionStorage.getItem('token')) ? true : false;
    if(token){
        return true;
    }else{
        return false;
    }
  }

  login(email:string, password:string){
    const url = this.APIBASEURL + 'api/login';
    let dataToPost = {
      'email': email,
      'password': password
    };
    return this.__httpClient.post( url, dataToPost, this.HEADERS ).pipe(
      map((resp:any)=>{
        if(resp.status == 1){
          sessionStorage.setItem('token', ''+resp.result.id);
        }
        return resp;
      })
    );
  }

  logout(){
    sessionStorage.removeItem('token');
  }
}
