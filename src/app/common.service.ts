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
  constructor(
    private __httpClient: HttpClient,
    private __router: Router
  ) { 
    this.APIBASEURL = AppGlobals.apiBaseUrl;
  }

  aboutUs(){
    var url = this.APIBASEURL + 'api/about-us';
    return this.__httpClient.get( url );
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
                title: result[i].parent.name, type: 'link', path: '/'+result[i].parent.slug,
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

  /* POST API EXAMPLE
  user(username:string, password:string, email:string, mobileNumber:string){
    var url = this.APIBASEURL + '/user/user';
    var dataToPost = {
      'username': username,
      'password': password,
      'email': email,
      'mobileNumber':mobileNumber
    };
    return this._httpClient.post(url, dataToPost);
  }

  randomName(gender:string){
    var url = this.APIBASEURL + '/scrap/randomName?gender='+gender;
    return this._httpClient.get( url ).pipe(
      map(
        (resp:any) => {
          this.all_random_names = resp.data;
          this.prepare_wheel_data();   
        }
      )
    );
  }
  */
}
