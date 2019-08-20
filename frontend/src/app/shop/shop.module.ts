import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { ShopRoutingModule } from './shop-routing.module';
import { SharedModule } from '../shared/shared.module';
import { SlickCarouselModule } from 'ngx-slick-carousel';
import { BarRatingModule } from "ngx-bar-rating";
import { RangeSliderModule  } from 'ngx-rangeslider-component';
import { InfiniteScrollModule } from 'ngx-infinite-scroll';
import { NgxPayPalModule } from 'ngx-paypal';
import { NgxImgZoomModule } from 'ngx-img-zoom';
// Home-one components
import { HomeComponent } from './home/home.component';
import { SliderComponent } from './home/slider/slider.component';
import { CollectionBannerComponent } from './home/collection-banner/collection-banner.component';
import { ProductSliderComponent } from './home/product-slider/product-slider.component';
import { ParallaxBannerComponent } from './home/parallax-banner/parallax-banner.component';
import { ProductTabComponent } from './home/product-tab/product-tab.component';
import { ServicesComponent } from './home/services/services.component';
import { BlogComponent } from './home/blog/blog.component';
import { InstagramComponent } from './home/instagram/instagram.component';
import { LogoComponent } from './home/logo/logo.component';

// Home-seven components
import { HomeSevenComponent } from './home-7/home-seven.component';
import { SliderSevenComponent } from './home-7/slider/slider.component';
import { CollectionBannerSevenComponent } from './home-7/collection-banner/collection-banner.component';
import { SpecialProductsSevenComponent } from './home-7/special-products/special-products.component';
import { ProductTabSevenComponent } from './home-7/product-tab/product-tab.component';
import { ProductSliderSevenComponent } from './home-7/product-slider/product-slider.component';
import { BlogSevenComponent } from './home-7/blog/blog.component';
import { ServicesSevenComponent } from './home-7/services/services.component';
import { InstagramSevenComponent } from './home-7/instagram/instagram.component';

// home-nine components
import { HomeNineComponent } from './home-9/home-nine.component';
import { HomeBannerComponent } from './home-9/home-banner/home-banner.component';
import { CollectionBannerNineComponent } from './home-9/collection-banner/collection-banner.component';
import { ProductTabNineComponent } from './home-9/product-tab/product-tab.component';

// Products Components 
import { ProductComponent } from './product/product.component';
import { ProductBoxComponent } from './product/product-box/product-box.component';
import { ProductBoxHoverComponent } from './product/product-box-hover/product-box-hover.component';
import { ProductBoxVerticalComponent } from './product/product-box-vertical/product-box-vertical.component';
import { ProductBoxMetroComponent } from './product/product-box-metro/product-box-metro.component';
import { CollectionLeftSidebarComponent } from './product/collection/collection-left-sidebar/collection-left-sidebar.component';
import { CollectionRightSidebarComponent } from './product/collection/collection-right-sidebar/collection-right-sidebar.component';
import { CollectionNoSidebarComponent } from './product/collection/collection-no-sidebar/collection-no-sidebar.component';
import { ColorComponent } from './product/collection/filter/color/color.component';
import { BrandComponent } from './product/collection/filter/brand/brand.component';
import { PriceComponent } from './product/collection/filter/price/price.component';
import { ProductLeftSidebarComponent } from './product/product-details/product-left-sidebar/product-left-sidebar.component';
import { ProductRightSidebarComponent } from './product/product-details/product-right-sidebar/product-right-sidebar.component';
import { ProductNoSidebarComponent } from './product/product-details/product-no-sidebar/product-no-sidebar.component';
import { ProductColLeftComponent } from './product/product-details/product-col-left/product-col-left.component';
import { ProductColRightComponent } from './product/product-details/product-col-right/product-col-right.component';
import { ProductColumnComponent } from './product/product-details/product-column/product-column.component';
import { ProductAccordianComponent } from './product/product-details/product-accordian/product-accordian.component';
import { ProductLeftImageComponent } from './product/product-details/product-left-image/product-left-image.component';
import { ProductRightImageComponent } from './product/product-details/product-right-image/product-right-image.component';
import { ProductVerticalTabComponent } from './product/product-details/product-vertical-tab/product-vertical-tab.component';
import { RelatedProductsComponent } from './product/product-details/related-products/related-products.component';
import { SidebarComponent } from './product/product-details/sidebar/sidebar.component';
import { CategoriesComponent } from './product/widgets/categories/categories.component';
import { QuickViewComponent } from './product/widgets/quick-view/quick-view.component';
import { ModalCartComponent } from './product/widgets/modal-cart/modal-cart.component';
import { NewProductComponent } from './product/widgets/new-product/new-product.component';
import { SearchComponent } from './product/search/search.component';
import { ProductCompareComponent } from './product/product-compare/product-compare.component';
import { WishlistComponent } from './product/wishlist/wishlist.component';
import { CartComponent } from './product/cart/cart.component';
import { CheckoutComponent } from './product/checkout/checkout.component';
import { SuccessComponent } from './product/success/success.component';
import { ExitPopupComponent } from './product/widgets/exit-popup/exit-popup.component';
import { AgeVerificationComponent } from './product/widgets/age-verification/age-verification.component';
import { NewsletterComponent } from './product/widgets/newsletter/newsletter.component';

@NgModule({
  exports: [ExitPopupComponent],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ShopRoutingModule,
    SharedModule,
    SlickCarouselModule,
    BarRatingModule,
    RangeSliderModule,
    InfiniteScrollModule,
    NgxPayPalModule,
    NgxImgZoomModule
  ],
  declarations: [
    // Home one
    HomeComponent,
    SliderComponent,
    CollectionBannerComponent,
    ProductSliderComponent,
    ParallaxBannerComponent,
    ProductTabComponent,
    ServicesComponent,
    BlogComponent,
    InstagramComponent,
    LogoComponent,
    
    // Home Seven
    HomeSevenComponent,
    SliderSevenComponent,
    CollectionBannerSevenComponent,
    SpecialProductsSevenComponent,
    ProductTabSevenComponent,
    ProductSliderSevenComponent,
    BlogSevenComponent,
    ServicesSevenComponent,
    InstagramSevenComponent,
    
    // Home Nine
    HomeNineComponent,
    HomeBannerComponent,
    CollectionBannerNineComponent,
    ProductTabNineComponent,
    
    // Product
    ProductComponent,
    ProductBoxComponent,
    ProductBoxHoverComponent,
    ProductBoxVerticalComponent,
    ProductBoxMetroComponent,
    CollectionLeftSidebarComponent,
    CollectionRightSidebarComponent,
    CollectionNoSidebarComponent,
    ColorComponent,
    BrandComponent,
    PriceComponent,
    ProductLeftSidebarComponent,
    ProductRightSidebarComponent,
    ProductNoSidebarComponent,
    ProductColLeftComponent,
    ProductColRightComponent,
    ProductColumnComponent,
    ProductAccordianComponent,
    ProductLeftImageComponent,
    ProductRightImageComponent,
    ProductVerticalTabComponent,
    RelatedProductsComponent,
    SidebarComponent,
    CategoriesComponent,
    QuickViewComponent,
    ModalCartComponent,
    NewProductComponent,
    SearchComponent,
    ProductCompareComponent,
    WishlistComponent,
    CartComponent,
    CheckoutComponent,
    SuccessComponent,
    ExitPopupComponent,
    AgeVerificationComponent,
    NewsletterComponent
  ]
})
export class ShopModule { }
