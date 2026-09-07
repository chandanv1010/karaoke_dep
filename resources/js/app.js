import Echo from 'laravel-echo';
import io from 'socket.io-client';

// Swiper phai la bien toan cuc TRUOC khi function.js / product.js duoc import,
// vi hai file do goi new Swiper(). Truoc day swiper duoc tai bang the <script>
// dong bo o cuoi body nen tinh co chay xong truoc module nay; khi cac script
// chuyen sang defer thi thu tu do khong con dam bao va sinh loi
// "Swiper is not defined". Bundle truc tiep vao day thi khong con phu thuoc
// thu tu the <script>, va bo duoc mot request rieng.
import Swiper from 'swiper/bundle';
window.Swiper = Swiper;

// import '../vendor/frontend/resources/library/js/jquery.js';

// window.jQuery = jQuery;
// window.$ = jQuery;

// import '../vendor/backend/js/plugins/toastr/toastr.min.js';
// // import '../vendor/frontend/resources/plugins/wow/dist/wow.min.js';
// import '../vendor/frontend/resources/uikit/js/uikit.min.js';
// import '../vendor/frontend/resources/uikit/js/components/sticky.min.js';
// import '../vendor/frontend/resources/uikit/js/components/accordion.min.js';
// import '../vendor/frontend/resources/uikit/js/components/accordion.min.js';
// import '../vendor/frontend/resources/uikit/js/components/lightbox.min.js';
// import '../vendor/frontend/resources/uikit/js/components/sticky.min.js';
// import '../vendor/frontend/core/plugins/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js';
import '../vendor/frontend/resources/function.js';
import '../vendor/frontend/core/library/product.js';
import '../vendor/frontend/core/library/filter.js';
import '../vendor/frontend/core/library/compare.js';
// import '../vendor/frontend/core/library/cart.js';
// import 'https://unpkg.com/swiper/swiper-bundle.min.js';



// window.io = io;

// window.Echo = new Echo({
//     broadcaster: 'socket.io',
//     host: 'http://laravelversion1.com:6001'
// });
