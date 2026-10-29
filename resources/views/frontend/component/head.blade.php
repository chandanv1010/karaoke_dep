{{-- Bon file CSS truoc day nam o day (font-awesome, uikit.modify, style.css,
     swiper-bundle) da duoc dua vao resources/css/app.scss de Vite gop chung
     vao mot file.

     Truoc: 5 stylesheet chan hien thi, 136,5 KiB truyen tai.
     Sau  : 1 stylesheet, 90,75 KiB gzip.
     PageSpeed bao "Render-blocking requests - Est savings of 1.430 ms" va day
     la tran cua diem mobile.

     Swiper van tu host (khong lay tu unpkg) nhu truoc, chi la gio nam trong
     bundle. Thu tu cascade giu nguyen y het - xem chu thich trong app.scss. --}}
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
