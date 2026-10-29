<?php
/**
 * Hoan nap thu vien Google den sau khi trang da tuong tac duoc.
 *
 * CHAY: php deploy/defer-tracking.php      (tu thu muc goc du an)
 * SAU DO BAT BUOC: php artisan cache:clear
 *   Khong xoa cache thi trang van phat ban script cu va thay nhu khong an gi.
 *
 * ----------------------------------------------------------------------
 * VAN DE
 * ----------------------------------------------------------------------
 * Tinh nguoc diem PageSpeed desktop (66):
 *     FCP  1.0s   diem ~0.96  x 10%  =  9.6
 *     LCP  1.4s   diem ~0.93  x 25%  = 23.3
 *     CLS  0.001  diem  1.00  x 25%  = 25.0
 *                                      ----
 *                                      57.9
 * Con lai 8 diem cho TBT (30%) va Speed Index (10%). Tuc la TBT gan bang 0.
 *
 * TBT do thoi gian luong chinh bi CHAN. Thu pham la chinh thu vien cua
 * Google: the <script async src="gtag/js"> tai song song that, nhung khi
 * file ve thi no CHAY tren luong chinh, roi tu no nap tiep hai goi dich cho
 * hai ma AW va ban ba luot /pagead/viewthroughconversion/. Tat ca roi dung
 * vao khoang giua FCP va TTI - dung khoang Lighthouse tinh TBT.
 *
 * ----------------------------------------------------------------------
 * CACH SUA
 * ----------------------------------------------------------------------
 * Tach lam hai phan:
 *   - window.dataLayer va ham gtag() dinh nghia NGAY. Moi loi goi
 *     gtag('config', ...) / gtag('event', ...) chi day vao mang dataLayer,
 *     nen chung xep hang binh thuong du thu vien chua ve.
 *   - Rieng viec NAP thu vien thi doi den khi:
 *       (a) khach cham vao trang - cuon, re chuot, cham, go phim, bam; hoac
 *       (b) 7 giay sau su kien load, tuy cai nao den truoc.
 *     Khi thu vien ve, no xu ly ca hang doi -> khong mat su kien nao.
 *
 * Vi sao cach nay an diem: TBT chi tinh cac tac vu dai nam GIUA FCP va TTI.
 * TTI la cua so 5 giay dau tien ma luong chinh yen tinh sau FCP. Day viec
 * cua Google ra sau moc do thi no khong con nam trong khoang duoc tinh.
 *
 * ----------------------------------------------------------------------
 * DANH DOI - DOC KY
 * ----------------------------------------------------------------------
 * Phien nao mo trang roi dong ngay ma KHONG he cuon / re chuot / cham, va
 * dong truoc moc 7 giay, thi se khong duoc ghi nhan trong GA4.
 *
 * Thuc te nhom nay rat nho: tren may tinh chi can nhich chuot qua trang la
 * mousemove ban; tren dien thoai chi can vuot mot cai la scroll hoac
 * touchstart ban. Theo doi CHUYEN DOI cua Google Ads khong bi anh huong, vi
 * chuyen doi luon di kem thao tac (bam goi dien, gui form) - ma thao tac thi
 * da kich hoat nap thu vien tu truoc do.
 *
 * Muon do that chinh xac tung luot xem hon la an diem: giam DELAY_MS xuong,
 * hoac lui han ve ban cu theo huong dan ben duoi. Doi lai TBT se quay lai va
 * desktop tut ve khoang 66.
 *
 * ----------------------------------------------------------------------
 * LUI LAI
 * ----------------------------------------------------------------------
 * Script nay luu ban dang chay vao systems_tracking_history TRUOC KHI sua,
 * kem ghi chu. Xem cac ban da luu:
 *     SELECT id, system_id, note, saved_at, LENGTH(content)
 *       FROM systems_tracking_history ORDER BY id;
 * Tra lai mot ban cu the:
 *     UPDATE systems s
 *       JOIN systems_tracking_history h ON h.system_id = s.id
 *        SET s.content = h.content
 *      WHERE h.id = <id_cua_ban_muon_lui>;
 *     php artisan cache:clear
 *
 * LUU Y: bang systems_tracking_backup (ban truoc khi gop ma) van con nguyen,
 * script nay KHONG ghi de len no - no ghi sang bang history rieng, vi
 * systems_tracking_backup lay id lam khoa chinh nen ghi lai se mat ban goc.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const DELAY_MS = 7000;   // moc du phong, tinh tu su kien load

$header = <<<'HTML'
<!-- Google tag (gtag.js) + Zalo.
     Nap MOT LAN cho ca ba ma, va chi nap SAU KHI trang da tuong tac duoc.
     Ly do, danh doi va cach lui lai: deploy/defer-tracking.php -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-2XESVP24XC');
  gtag('config', 'AW-578170159');
  gtag('config', 'AW-11407116052');
  gtag('event', 'page_view', { 'send_to': 'AW-578170159' });

  (function () {
    var done = false;
    var EV = ['scroll', 'mousemove', 'pointerdown', 'touchstart', 'keydown', 'wheel', 'click'];

    function inject(src) {
      var s = document.createElement('script');
      s.async = true;
      s.src = src;
      document.head.appendChild(s);
    }

    function load() {
      if (done) return;
      done = true;
      inject('https://www.googletagmanager.com/gtag/js?id=G-2XESVP24XC');
      inject('https://s.zzcdn.me/ztr/ztracker.js?id=7118134106671710208');
    }

    for (var i = 0; i < EV.length; i++) {
      window.addEventListener(EV[i], load, { once: true, passive: true });
    }

    function arm() { window.setTimeout(load, __DELAY__); }
    if (document.readyState === 'complete') arm();
    else window.addEventListener('load', arm, { once: true });
  })();
</script>
HTML;

$header = str_replace('__DELAY__', (string) DELAY_MS, $header);

// --- 1. Luu ban dang chay vao bang lich su ------------------------------
if (! Schema::hasTable('systems_tracking_history')) {
    DB::statement('CREATE TABLE `systems_tracking_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `system_id` INT NOT NULL,
        `keyword` VARCHAR(191) NULL,
        `content` LONGTEXT NULL,
        `note` VARCHAR(255) NULL,
        `saved_at` DATETIME NULL,
        KEY `system_id` (`system_id`)
    ) DEFAULT CHARSET=utf8mb4');
    echo "da tao bang systems_tracking_history\n";

    // Mang luon cac ban trong systems_tracking_backup sang, de tat ca cac moc
    // deu nam mot cho va khong con nguy co ghi de.
    if (Schema::hasTable('systems_tracking_backup')) {
        $n = 0;
        foreach (DB::table('systems_tracking_backup')->get() as $b) {
            DB::table('systems_tracking_history')->insert([
                'system_id' => $b->id,
                'keyword'   => $b->keyword ?? null,
                'content'   => $b->content,
                'note'      => 'truoc khi gop ma (tu systems_tracking_backup)',
                'saved_at'  => $b->saved_at ?? null,
            ]);
            $n++;
        }
        echo "da mang $n ban tu systems_tracking_backup sang\n";
    }
}

$row = DB::table('systems')->where('id', 88)->first();
if (! $row) {
    exit("KHONG TIM THAY systems id=88 - dung lai, khong sua gi\n");
}

DB::table('systems_tracking_history')->insert([
    'system_id' => 88,
    'keyword'   => $row->keyword ?? null,
    'content'   => $row->content,
    'note'      => 'truoc khi hoan nap thu vien',
    'saved_at'  => now(),
]);
echo 'da luu ban dang chay (' . strlen($row->content) . " bytes)\n";

// --- 2. Ghi ban moi ------------------------------------------------------
DB::table('systems')->where('id', 88)->update(['content' => $header]);
echo 'da ghi ban moi (' . strlen($header) . " bytes)\n\n";

// --- 3. Doi chieu --------------------------------------------------------
$c = DB::table('systems')->where('id', 88)->value('content');
printf("the <script src> nap thang trong HTML : %d  (phai la 0)\n", substr_count($c, '<script async src'));
printf("so lan goi gtag('config', ...)        : %d  (phai la 3)\n", substr_count($c, "gtag('config'"));
printf("co hang doi dataLayer                 : %s\n", str_contains($c, 'dataLayer.push') ? 'co' : 'KHONG');
printf("moc du phong                          : %d ms\n", DELAY_MS);
echo "\nCON MOT BUOC NUA: php artisan cache:clear\n";
