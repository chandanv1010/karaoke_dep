<link href="{{ asset('templates/frontend/resources/fonts/font-awesome-4.4.0/css/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ asset('templates/frontend/resources/uikit/css/uikit.modify.css') }}" rel="stylesheet">
<link href="{{ asset('templates/frontend/resources/style.css') }}" rel="stylesheet">
{{-- Swiper: tu host thay vi tai tu unpkg.com. Bo mot lan bat tay DNS/TLS ra
     CDN ngoai va khong con phu thuoc do on dinh cua unpkg. --}}
<link href="{{ asset('templates/frontend/resources/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

{{-- CSS cua vite giu nguyen vi tri (sau style.css) de thu tu cascade khong doi. --}}
@vite('resources/css/app.scss')

{{--
    jQuery va UIkit truoc day khong co defer nen chan hoan toan viec ve trang.
    Khong the day xuong cuoi body vi mot so view co inline $(document).ready()
    nam giua noi dung, se chay truoc khi jQuery kip tai.

    Dung defer: van tai song song, chi hoan THUC THI den sau khi parse xong HTML
    va luon xong TRUOC DOMContentLoaded. Cac doan inline da doi sang lang nghe
    DOMContentLoaded nen $ chac chan da co.

    THU TU QUAN TRONG: app.js cua vite la module (cung defer) va import
    function.js / product.js - nhung file nay dung jQuery. Script defer va module
    thuc thi theo dung thu tu tai lieu, nen app.js PHAI dat sau jQuery. Truoc day
    jQuery la script dong bo nen luon chay truoc; khi doi sang defer thi vi tri
    trong tai lieu moi la thu quyet dinh.
--}}
<script defer src="{{ asset('templates/frontend/resources/library/js/jquery.min.js') }}"></script>
<script defer src="{{ asset('templates/frontend/resources/uikit/js/uikit.min.js') }}"></script>
{{-- Swiper da duoc bundle vao app.js (resources/js/app.js) va gan vao
     window.Swiper, nen khong con the <script> rieng cho swiper nua. --}}
@vite('resources/js/app.js')
