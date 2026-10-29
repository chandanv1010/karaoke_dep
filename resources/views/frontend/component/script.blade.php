{{--
    Tat ca dat defer: tai song song, thuc thi theo dung thu tu tai lieu va xong
    truoc DOMContentLoaded. Truoc day cac file nay chay dong bo, moi file chan
    mot nhip parse.
--}}
{{-- 5 component cua UIkit (slider, slideshow, sticky, lightbox, accordion) da
     duoc gop thanh mot file: 5 request con 1, giu nguyen thu tu nap. --}}
<script defer src="{{ asset('templates/frontend/resources/uikit/js/uikit-components.min.js') }}"></script>
{{-- Swiper duoc bundle thang vao resources/js/app.js, khong con the <script>
     rieng. --}}
{{-- Da bo jquery.flexslider-min.js (22 KB): ca frontend desktop khong he goi
     .flexslider() o dau - lan goi duy nhat nam trong cac file demo cua chinh
     plugin. Cac template mobile/* dung flexslider that su va co the <script>
     rieng cua chung, khong bi anh huong. --}}
<script defer src="{{ asset('templates/frontend/resources/function.js') }}"></script>
<script defer src="{{ asset('templates/frontend/resources/js/karaoke-home.js') }}"></script>
<script>
    // Boc trong DOMContentLoaded vi jQuery gio tai bang defer: doan nay chay
    // luc parse, con $ chi ton tai sau khi script defer thuc thi.
    document.addEventListener('DOMContentLoaded', function () {
        $(window).scroll(function () {
            $(this).scrollTop() > 200
                ? $("#goTop").stop().animate({ bottom: "60px" }, 500)
                : $("#goTop").stop().animate({ bottom: "-60px" }, 500);
        });

        $("#goTop").click(function (e) {
            e.preventDefault();
            $("html, body").animate({ scrollTop: 0 }, 500);
        });

        $("img.lazy").each(function () {
            if (!$(this).attr("src")) $(this).attr("src", $(this).data("original"));
        });

        $('.support-fx .heading').click(function () {
            $('.support-fx').toggleClass('hide');
        });
    });
</script>
