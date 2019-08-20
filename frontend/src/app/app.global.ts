import { Injectable } from '@angular/core';

@Injectable()
export class AppGlobals {
  public static get apiBaseUrl(): string {
    return 'http://plentay.lotzap.com/';
  }
}