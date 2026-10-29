{{-- Bon file CSS truoc day nam o day (font-awesome, uikit.modify, style.css,
     swiper-bundle) da duoc dua vao resources/css/app.scss de Vite gop chung
     vao mot file.

     Truoc: 5 stylesheet chan hien thi, 136,5 KiB truyen tai.
     Sau  : 1 stylesheet, 90,75 KiB gzip.
     PageSpeed bao "Render-blocking requests - Est savings of 1.430 ms" va day
     la tran cua diem mobile.

     Swiper van tu host (khong lay tu unpkg) nhu truoc, chi la gio nam trong
     bundle. Thu tu cascade giu nguyen y het - xem chu thich trong app.scss. --}}
{{-- CSS gop lam mot file, tai theo kieu chan hien thi.

     DA THU nhung CSS toi thieu cho phan dau trang chu va tai file day du
     khong chan (commit b94080c). Ket qua do tren production:

                        FCP      CLS      diem
       truoc khi thu    4,8s     0,002    58
       sau, lan 1       2,4s     0,039    54
       sau, lan 2       2,4s     0,039    58
       sau, lan 3       2,4s     1,039    38

     FCP giam mot nua that, nhung CLS mat on dinh va co lan vot len 1,039 -
     tuc khach that thay trang nhay manh luc moi tai. Diem thi khong hon
     duoc trang thai cu, ma trang thai cu co CLS 0,002.

     Nguyen nhan: bo CSS toi thieu 9,6 KB chua phu het phan dau trang; cho
     nao thieu thi ve bang kieu mac dinh roi nhay khi file day du gan vao.
     Muon lam cho dung phai tach CSS toi thieu RIENG cho tung loai trang va
     doi chieu anh chup tung trang - khong phai mot bo dung chung.

     Hai file deploy/gen-critical-css.py va deploy/critical-selectors.md van
     giu lai de lan sau lam tiep. --}}
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
