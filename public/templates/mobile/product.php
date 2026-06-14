<!DOCTYPE html>
<html>
<head>
	<?php require_once 'inc/head.php'; ?>
</head>
<body>
	<?php require_once 'inc/header.php'; ?>
	<section id="body">
		<div class="fc-breadcrumb uk-margin-bottom uk-margin-top">
			<div class="uk-container uk-container-center">
				<ul class="uk-breadcrumb uk-margin-remove">
					<li><a href="" title="Trang chủ">Trang chủ</a></li>
					<li><a href="" title="">TPCN Giảm Cân</a></li>
					<li class="uk-active"><a href="" title="">Thực phẩm chức năng - Viên uống làm trắng da</a></li>
				</ul>
			</div><!-- .uk-container -->
		</div><!-- end .fc-breadcrumb -->
		<section class="block-product">
			<div class="uk-container uk-container-center">
				<div class="uk-panel product-detail">
					<div class="fc-product">
						<div class="fc-product-slideshow">
							<div id="slider" class="flexslider uk-margin-bottom uk-text-center">
								<ul class="slides">
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
								</ul>
							</div>
							<div id="carousel" class="flexslider uk-margin-bottom-remove">
								<ul class="slides">
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/nhauthaicuu-green-healthfood.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
									<li><div class="fc-thumb"><img src="images/upload/meiji-amino-collagen-white.png" alt="" /></div></li>
								</ul>
							</div>
							<script>
								$(window).load(function(){
									$('#carousel').flexslider({ animation: "slide", controlNav: false, animationLoop: false, slideshow: false, itemWidth: 180, itemMargin: 4, asNavFor: '#slider' });
									$('#slider').flexslider({ animation: "slide", controlNav: false, animationLoop: false, slideshow: false, sync: "#carousel", start: function(slider){ $('body').removeClass('loading'); } });
								});
							</script>
						</div><!-- end .fc-product-slideshow -->
					</div><!-- end .fc-product -->
				</div><!-- end uk-panel -->

				<div class="uk-panel product-info">
					<div class="uk-panel-title uk-margin-remove">
						<ul class="uk-tab fc-tabs" data-uk-tab="{connect:'#product-tabs'}">
							<li class="uk-active"><a>Thông tin</a></li>
							<li><a>Đánh Giá</a></li>
							<li><a>Thư viện hình ảnh</a></li>
						</ul>
					</div>
					<div class="fc-panel-body uk-margin-remove">
						<ul id="product-tabs" class="uk-switcher">
							<li>
								<h2>Lorem ipsum dolor sit amet. </h2>
								<p>
									Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet repellendus, asperiores perspiciatis iure vero, suscipit odit ab deserunt mollitia, voluptates, reiciendis est. Unde minus, doloremque! Ea, molestias. Accusamus, ab rem.
								</p>
							</li>
							<li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt, eligendi.</li>
							<li>Lorem ipsum dolor sit amet.</li>
						</ul>
					</div><!-- .fc-panel-body -->
				</div><!-- .uk-panel -->

				<div class="uk-panel coment-fb uk-margin-small-top">
					<div class="fc-panel-body">
						<div class="fb-comments" data-href="binh-sieu-toc-happycook-hs17sk-1-7-lit-p24.html" data-width="100%" data-numposts="3"></div>
						</div><!-- .fc-panel-body -->
				</div>
			</div><!-- end .uk-container -->
		</section><!-- end .product-block -->

		<section class="block product-related uk-margin-top">
			<div class="uk-container uk-container-center">
				<div class="uk-panel">
					<header class="uk-clearfix">
						<h3 class="block-title uk-margin-remove uk-float-left"><a href="" title="" class="fc-text-uppercase">Sản phẩm cùng loại</a></h3>
					</header><!-- header -->
					<div class="fc-body">
						<div class="uk-grid uk-grid-collapse uk-grid-width-medium-1-4 uk-grid-width-small-1-2">
							<div class="fc-product">
								<div class="fc-product-thumb">
									<a href="" class="fc-fit-img" title=""><img src="images/upload/meiji-amino-collagen-white.png" alt=""></a>
								</div>
								<div class="fc-product-title uk-margin-bottom">
									<a href="" class="uk-text-bold" title="">Meiji Amino Collagen White 500mg dạng viên – Collagen...</a>
								</div>
								<div class="fc-product-price"> 
									<div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">800.000đ</span></div>								
								</div>
								<div class="fc-product-link"><a href="" title="" class="uk-button">Xem chi tiết</a></div>
							</div>
							<div class="fc-product">
								<div class="fc-product-thumb">
									<a href="" class="fc-fit-img" title=""><img src="images/upload/ivory-caps.png" alt=""></a>
								</div>
								<div class="fc-product-title uk-margin-bottom">
									<a href="" class="uk-text-bold" title="">Meiji Amino Collagen White 500mg dạng viên – Collagen...</a>
								</div>
								<div class="fc-product-price"> 
									<div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">800.000đ</span></div>								
								</div>
								<div class="fc-product-link"><a href="" title="" class="uk-button">Xem chi tiết</a></div>
							</div>
							<div class="fc-product">
								<div class="fc-product-thumb">
									<a href="" class="fc-fit-img" title=""><img src="images/upload/ivory-caps-vitamin-c.png" alt=""></a>
								</div>
								<div class="fc-product-title uk-margin-bottom">
									<a href="" class="uk-text-bold" title="">Meiji Amino Collagen White 500mg dạng viên – Collagen...</a>
								</div>
								<div class="fc-product-price"> 
									<div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">800.000đ</span></div>								
								</div>
								<div class="fc-product-link"><a href="" title="" class="uk-button">Xem chi tiết</a></div>
							</div>
							<div class="fc-product">
								<div class="fc-product-thumb">
									<a href="" class="fc-fit-img" title=""><img src="images/upload/nhauthaicuu-green-healthfood.png" alt=""></a>
								</div>
								<div class="fc-product-title uk-margin-bottom">
									<a href="" class="uk-text-bold" title="">Meiji Amino Collagen White 500mg dạng viên – Collagen...</a>
								</div>
								<div class="fc-product-price"> 
									<div class="fc-product-price-new uk-margin-small-bottom"><span class="uk-text-bold">800.000đ</span></div>								
								</div>
								<div class="fc-product-link"><a href="" title="" class="uk-button">Xem chi tiết</a></div>
							</div>
						</div>
					</div><!-- end fc-body -->
				</div><!-- end .uk-panel -->
			</div><!-- end .uk-container -center -->
		</section><!-- end .block -->
	</section><!-- end #body -->
	<?php require_once 'inc/footer.php'; ?>
	<?php //require_once 'inc/offcanvas.php'; ?>
	<?php require_once 'inc/script.php'; ?>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.5"; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script>
	<script src="https://apis.google.com/js/platform.js" async defer>{lang: 'vi'}</script>
</body>
</html>