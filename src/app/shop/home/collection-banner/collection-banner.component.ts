import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-collection-banner',
  templateUrl: './collection-banner.component.html',
  styleUrls: ['./collection-banner.component.scss']
})
export class CollectionBannerComponent implements OnInit {

  constructor() { }

  ngOnInit() { }

  // Collection banner
  public category = [{
    image: 'assets/images/fashion/1.jpg',
    save: 'save 50%',
    title: 'women',
    link: '/home/left-sidebar/collection/women'
  }, {
    image: 'assets/images/fashion/5.jpg',
    save: 'save 30%',
    title: 'men',
    link: '/home/left-sidebar/collection/men'
  }, {
    image: 'assets/images/fashion/3.jpg',
    save: 'save 40%',
    title: 'men',
    link: '/home/left-sidebar/collection/men'
  }, {
    image: 'assets/images/fashion/4.jpg',
    save: 'save 20%',
    title: 'men',
    link: '/home/left-sidebar/collection/men'
  }, {
    image: 'assets/images/fashion/2.jpg',
    save: 'save 50%',
    title: 'men',
    link: '/home/left-sidebar/collection/men'
  }, {
    image: 'assets/images/fashion/6.jpg',
    save: 'save 10%',
    title: 'men',
    link: '/home/left-sidebar/collection/men'
  }]

}
