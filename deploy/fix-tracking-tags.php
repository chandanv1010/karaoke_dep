<?php
/**
 * Gop cac ma theo doi Google ve mot lan nap thu vien.
 *
 * CHAY: php deploy/fix-tracking-tags.php   (tu thu muc goc cua du an)
 *
 * Dung PHP thay vi file .sql vi noi dung tracking co san dau chay (;) ben
 * trong chuoi - tach file SQL theo dau chay la cat nham giua cau lenh.
 *
 * Chi tiet van de va cach lui lai: xem deploy/fix-tracking-tags.sql
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$header = <<<'HTML'
<!-- Google tag (gtag.js) - nap MOT LAN cho ca ba ma -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-2XESVP24XC"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-2XESVP24XC');
  gtag('config', 'AW-578170159');
  gtag('config', 'AW-11407116052');
  gtag('event', 'page_view', { 'send_to': 'AW-578170159' });
</script>
<script async src="https://s.zzcdn.me/ztr/ztracker.js?id=7118134106671710208"></script>
HTML;

$ids = [88, 89, 131, 132];

// 1. Sao luu ban cu
if (! Schema::hasTable('systems_tracking_backup')) {
    DB::statement('CREATE TABLE `systems_tracking_backup` (
        `id` INT PRIMARY KEY,
        `keyword` VARCHAR(191) NULL,
        `content` LONGTEXT NULL,
        `saved_at` DATETIME NULL
    )');
    echo "da tao bang systems_tracking_backup\n";
}

foreach (DB::table('systems')->whereIn('id', $ids)->get() as $row) {
    DB::table('systems_tracking_backup')->updateOrInsert(
        ['id' => $row->id],
        ['keyword' => $row->keyword ?? null, 'content' => $row->content, 'saved_at' => now()]
    );
}
echo "da sao luu " . count($ids) . " dong\n";

// 2. Gop
DB::transaction(function () use ($header) {
    DB::table('systems')->where('id', 88)->update(['content' => $header]);
    DB::table('systems')->whereIn('id', [89, 131, 132])->update(['content' => '']);
});

echo "da gop xong\n\n";

// 3. Doi chieu lai
foreach (DB::table('systems')->whereIn('id', $ids)->orderBy('id')->get() as $r) {
    printf("id=%-4s %-16s %d bytes  gtag/js: %d lan\n",
        $r->id, $r->keyword ?? '?', strlen($r->content), substr_count($r->content, 'gtag/js'));
}
