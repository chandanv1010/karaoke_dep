<?php
/**
 * Bo sung meta description cho 3 trang danh muc khong co chu nao, va cap nhat
 * lien ket mang xa hoi trong Cau hinh chung. Co sao luu truoc.
 *
 *     php deploy/fix-seo-meta.php            # chi xem truoc, KHONG ghi
 *     php deploy/fix-seo-meta.php --apply    # ghi that, roi: php artisan cache:clear
 *
 * Theo bao cao kiem toan 04/11/2026:
 *   - thong-tin / van-ban-phap-luat / kinh-nghiem-thi-cong: ca description lan
 *     content deu rong, nen seo() khong co gi de lam mo ta du phong. Mo ta duoi
 *     day viet theo dung cac bai dang co trong tung danh muc (xem ngay 04/11).
 *     Chi ghi khi meta_description dang rong - khong de len chu bien tap da viet.
 *   - seo_twitter: twitter.com -> x.com (cung tai khoan, x.com tra 200).
 *   - seo_google: lien ket Google+ (dich vu dong cua 2019). Header da bo icon;
 *     xoa gia tri de khong cho nao khac dung lai.
 *
 * Sao luu vao `seo_content_backup` (note 'fix-seo-meta').
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);
const NOTE = 'fix-seo-meta 04/11/2026';

const DESCRIPTIONS = [
    'thong-tin' => 'Thông tin hữu ích từ AZKTV cho chủ quán karaoke: mẫu hợp đồng thi công, chuyển nhượng quán, hồ sơ PCCC và thủ tục cấp phép kinh doanh karaoke.',
    'van-ban-phap-luat' => 'Tổng hợp văn bản pháp luật về kinh doanh karaoke: Luật PCCC, Thông tư 06 của Bộ Công an về điều kiện PCCC, mẫu hợp đồng thiết kế và thi công quán karaoke.',
    'kinh-nghiem-thi-cong' => 'Kinh nghiệm thiết kế, thi công quán karaoke từ AZKTV: nội thất phòng hát, decor tối giản, hệ thống âm thanh, mô hình karaoke mini và ý tưởng trang trí.',
];

const SYSTEMS = [
    'seo_twitter' => ['https://twitter.com/haikaraokedep', 'https://x.com/haikaraokedep'],
    'seo_google'  => ['https://plus.google.com/u/1/105940201758671515182', ''],
];

function backup(string $table, $rowId, string $col, $old): void
{
    DB::table('seo_content_backup')->insert([
        'table_name' => $table, 'row_id' => $rowId, 'language_id' => 1,
        'column_name' => $col, 'old_value' => $old, 'note' => NOTE, 'saved_at' => now(),
    ]);
}

foreach (DESCRIPTIONS as $canonical => $desc) {
    $row = DB::table('post_catalogue_language')->where('language_id', 1)->where('canonical', $canonical)->first();
    if (!$row) {
        echo "  [bo qua] khong thay danh muc $canonical\n";
        continue;
    }
    if (trim((string) $row->meta_description) !== '') {
        echo "  [bo qua] $canonical da co mo ta: {$row->meta_description}\n";
        continue;
    }
    echo "  $canonical (" . mb_strlen($desc) . " ky tu)\n      $desc\n";
    if ($apply) {
        backup('post_catalogue_language', $row->post_catalogue_id, 'meta_description', $row->meta_description);
        DB::table('post_catalogue_language')->where('language_id', 1)->where('post_catalogue_id', $row->post_catalogue_id)
            ->update(['meta_description' => $desc]);
    }
}

foreach (SYSTEMS as $keyword => [$from, $to]) {
    $row = DB::table('systems')->where('language_id', 1)->where('keyword', $keyword)->first();
    if (!$row || $row->content !== $from) {
        echo "  [bo qua] systems.$keyword = " . ($row->content ?? '-') . "\n";
        continue;
    }
    echo "  systems.$keyword: $from -> " . ($to === '' ? '(rong)' : $to) . "\n";
    if ($apply) {
        backup('systems', $row->id, $keyword, $row->content);
        DB::table('systems')->where('id', $row->id)->update(['content' => $to]);
    }
}

echo "\n" . ($apply ? "DA GHI - nho chay: php artisan cache:clear\n" : "XEM TRUOC (them --apply de ghi that)\n");
