<script src="{{ asset('templates/frontend/resources/uikit/js/components/slider.min.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/uikit/js/components/slideshow.min.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/uikit/js/components/sticky.min.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/uikit/js/components/lightbox.min.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/uikit/js/components/accordion.min.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/plugins/flex-slider/jquery.flexslider-min.js') }}"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="{{ asset('templates/frontend/resources/function.js') }}"></script>
<script src="{{ asset('templates/frontend/resources/js/karaoke-home.js') }}"></script>
<script>
    $(window).scroll(function(){
        $(this).scrollTop() > 200 ? $("#goTop").stop().animate({bottom:"60px"},500) : $("#goTop").stop().animate({bottom:"-60px"},500);
    });
    $(document).ready(function(){
        $("#goTop").click(function(e){ e.preventDefault(); $("html, body").animate({scrollTop:0},500); });
        $("img.lazy").each(function(){ if(!$(this).attr("src")) $(this).attr("src", $(this).data("original")); });
        $('.support-fx .heading').click(function(){ $('.support-fx').toggleClass('hide'); });
    });
</script>
