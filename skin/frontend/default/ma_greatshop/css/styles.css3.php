<?php
    header('Content-type: text/css; charset: UTF-8');
    header('Cache-Control: must-revalidate');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    $url = $_REQUEST['url'];
?>

.mobilemenu>li.first {
	-webkit-border-radius: 4px 4px 0 0;
	-moz-border-radius: 4px 4px 0 0;
	border-radius: 4px 4px 0 0;
	behavior: url(<?php echo $url; ?>css/css3.htc);
	position: relative;
}
.mobilemenu>li.last {
	-webkit-border-radius:0 0 4px 4px;
	-moz-border-radius: 0 0 4px 4px;
	border-radius: 0 0 4px 4px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
	position: relative;
}
.block {
    -moz-box-shadow: 0 0 1px 1px #ddd;
	-webkit-box-shadow: 0 0 1px 1px #ddd;
	box-shadow: 0 0 1px 1px #ddd;
}
.ma-newproductslider-container .price-box-item .price-box,
.ma-featuredproductslider-container .price-box-item .price-box,
.product-view .product-shop .price-box-item .price-box,
.ma-upsellslider-container .price-box-item .price-box,
.products-list .price-box-item .price-box, 
.ma-newproductslider-container .newproductslider-item:hover,
.ma-featuredproductslider-container .featuredproductslider-item:hover,
.ma-upsellslider-container .newproductslider-item:hover,
.products-grid .item:hover,
.products-list li.item:hover {
	-moz-box-shadow: 0 0 2px 2px #ddd;
	-webkit-box-shadow: 0 0 2px 2px #ddd;
	box-shadow: 0 0 2px 2px #ddd;
}
.top-cart-icon, .ma-newproductslider-container .flex-direction-nav a,
.ma-featuredproductslider-container .flex-direction-nav a,
.footer .social li a, .ma-thumbnail-container .flex-direction-nav a,
.ma-upsellslider-container .flex-direction-nav a {
    -webkit-border-radius: 25px;
	-moz-border-radius: 25px;
	border-radius: 25px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
}
.ma-newproductslider-container .price-box-item .price-box,
.ma-featuredproductslider-container .price-box-item .price-box,
.product-view .product-shop .price-box-item .price-box,
.ma-upsellslider-container .price-box-item .price-box,
.products-grid .price-box-item .price-box,
.products-list .price-box-item .price-box,
#back-top {
    -webkit-border-radius: 50px;
	-moz-border-radius: 50px;
	border-radius: 50px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
}
button.btn-cart span,
.ma-newproductslider-container .add-to-links .link-compare,
.ma-newproductslider-container .add-to-links .link-wishlist,
.ma-featuredproductslider-container .add-to-links .link-compare,
.ma-featuredproductslider-container .add-to-links .link-wishlist,
.block-subscribe input.input-text,
.block-subscribe .actions button.button span, .email-friend a,
.add-to-links .link-compare, .add-to-links .link-wishlist,
#search_price span, button.button span {
    -webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
}
.ma-newproductslider-container .newproductslider-item:hover img,
.ma-featuredproductslider-container .featuredproductslider-item:hover img,
.ma-upsellslider-container .newproductslider-item:hover .product-image img,
.products-grid .item:hover img{
    -moz-opacity: .5;
    -webkit-opacity: .5;
    -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=70)";/*IE8*/ opacity:.5;
    }
.ma-newproductslider-container .add-to-links button.btn-cart,
.ma-featuredproductslider-container .add-to-links button.btn-cart,
.products-grid .add-to-links button.btn-cart {
    -webkit-transition: all 1s ease;
  -moz-transition: all 1s ease;
  -o-transition: all 1s ease;
  transition: all 1s ease;
}
.products-grid .item .product-name,
.products-grid .item .ratings,
.products-grid .item .actions,
.products-grid .item .product-des,
.ma-newproductslider-container .add-to-links .link-wishlist,
.ma-featuredproductslider-container .add-to-links .link-wishlist,
.products-grid .add-to-links .link-wishlist,
.ma-featuredproductslider-container .product-inner .product-name,
.ma-featuredproductslider-container .product-inner .ma-desc,
.ma-featuredproductslider-container .featuredproductslider-item .ratings,
.ma-featuredproductslider-container .product-inner .actions,
.ma-newproductslider-container .product-inner .product-name,
.ma-newproductslider-container .product-inner .ma-desc,
.ma-newproductslider-container .featuredproductslider-item .ratings,
.ma-newproductslider-container .product-inner .actions {
    -webkit-transition: all 1s ease;
  -moz-transition: all 1s ease;
  -o-transition: all 1s ease;
  transition: all 1s ease;
}
.ma-newproductslider-container .add-to-links .link-compare,
.ma-featuredproductslider-container .add-to-links .link-compare,
.products-grid .add-to-links .link-compare,
.ma-upsellslider-container .item-inner .product-name,
a, button.button span, .ma-featuredproductslider-container .product-inner .actions .add-to-links {
    -webkit-transition: all 0.5s ease;
  -moz-transition: all 0.5s ease;
  -o-transition: all 0.5s ease;
  transition: all 0.5s ease;
}

.ma-newproductslider-container .newproductslider-item .price-box-item .price-box,
.ma-featuredproductslider-container .featuredproductslider-item .price-box-item .price-box,
.products-grid .item .price-box-item .price-box {
-webkit-transition: all 1s ease;
  -moz-transition: all 1s ease;
  -o-transition: all 1s ease;
  transition: all 1s ease;
  }
.ma-newproductslider-container .newproductslider-item:hover .price-box-item .price-box,
.ma-featuredproductslider-container .featuredproductslider-item:hover .price-box-item .price-box,
.products-grid .item:hover .price-box-item .price-box {
    -webkit-transform: rotate(720deg);
    -moz-transform: rotate(720deg);
    -o-transform: rotate(720deg);
    -ms-transform: rotate(720deg);
    transform: rotate(720deg);
}
.block-subscribe input.input-text {
    -moz-box-shadow: inset 0 2px 2px 0 #6d6d6d;
	-webkit-box-shadow: inset 0 2px 2px 0 #6d6d6d;
	box-shadow: inset 0 2px 2px 0 #6d6d6d;
}
.block-banner-left:hover img,
.banner-right:hover img,
.home-banner-img img:hover,
.footer-static-top .static-box4:hover img,
div.popup .block1 .column img:hover,
static-menu-img img:hover{
    -moz-opacity:.7; -webkit-opacity:.7; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=70)";/*IE8*/ opacity:.7;
}