// Da bo hai dong import 'laravel-echo' va 'socket.io-client'.
// Ca hai deu KHONG duoc dung: window.Echo va window.io o cuoi file nay deu bi
// ghi chu tu lau. laravel-echo thi rollup tu loai duoc, nhung socket.io-client
// thi khong (goi nay co side effect nen khong bi cat), nen no van nam trong
// bundle: grep file da build thay socket.io-client, engine.io-client va
// socket.io-parser. Khoang 50 KB ma trinh duyet phai tai va phan tich cho mot
// tinh nang khong ton tai.
// Muon dung realtime tro lai thi cai lai va mo khoi Echo o duoi.

// Swiper phai la bien toan cuc TRUOC khi function.js / product.js duoc import,
// vi hai file do goi new Swiper(). Truoc day swiper duoc tai bang the <script>
// dong bo o cuoi body nen tinh co chay xong truoc module nay; khi cac script
// chuyen sang defer thi thu tu do khong con dam bao va sinh loi
// "Swiper is not defined". Bundle truc tiep vao day thi khong con phu thuoc
// thu tu the <script>, va bo duoc mot request rieng.
//
// Nap loi (swiper) + 4 module, thay cho 'swiper/bundle': ca site chi dung
// pagination, navigation, autoplay va thumbs, trong khi bundle keo theo ca 23
// module (coverflow, cube, flip, cards, parallax, virtual, zoom, a11y...).
// Swiper.use() dang ky module o cap toan cuc nen moi lenh new Swiper(...) san
// co giu nguyen, khong phai sua mot dong nao trong function.js / product.js.
// CSS khong anh huong: no den tu swiper-bundle.min.css duoc import trong
// resources/css/app.scss, khong phai tu goi JS nay.
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules';

Swiper.use([Navigation, Pagination, Autoplay, Thumbs]);
window.Swiper = Swiper;

import '../vendor/frontend/resources/function.js';
import '../vendor/frontend/core/library/product.js';
import '../vendor/frontend/core/library/filter.js';
import '../vendor/frontend/core/library/compare.js';
// import '../vendor/frontend/core/library/cart.js';

// Realtime (dang tat). Mo lai thi phai:
//   npm i laravel-echo socket.io-client
// roi bo ghi chu khoi duoi day.
// import Echo from 'laravel-echo';
// import io from 'socket.io-client';
// window.io = io;
// window.Echo = new Echo({
//     broadcaster: 'socket.io',
//     host: 'http://laravelversion1.com:6001'
// });
