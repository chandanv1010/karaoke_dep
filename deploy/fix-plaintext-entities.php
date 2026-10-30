<?php
/**
 * Giai ma thuc the HTML trong cac cot VAN BAN THUAN.
 *
 *     php deploy/fix-plaintext-entities.php
 *     php deploy/fix-plaintext-entities.php --apply
 *
 * VI SAO CAN TACH RIENG KHOI fix-content-urls.php
 *
 * Tieu de bai "Song Gio" luu trong CSDL la "Jack &amp; KICM". View in ra bang
 * {{ }} nen Blade escape THEM MOT LOP -> HTML thanh "&amp;amp;" -> nguoi doc
 * nhin thay nguyen chu "&amp;" tren trang va trong ket qua tim kiem.
 * Voi cac cot nay, gia tri trong CSDL phai la dau & TRAN.
 *
 * NHUNG cot `content` va `description` thi NGUOC LAI: chung duoc in bang
 * {!! !!} (HTML tho), nen "&amp;" o do la DUNG CHUAN. Giai ma chung se pha
 * vo ma HTML. Vi vay script nay chi dung toi bon cot duoi day, khong bao gio
 * dung toi content/description.
 *
 * Pham vi that su (dem ngay 30/10/2026): 5 gia tri tren toan site.
 *
 * Gia tri cu duoc ghi vao `seo_content_backup`, cung bang ma
 * fix-content-urls.php dung.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$apply = in_array('--apply', $argv, true);

/** CHI cac cot duoc in ra bang {{ }} (Blade tu escape). */
const PLAIN_COLUMNS = ['name', 'meta_title', 'meta_description', 'meta_keyword'];

const TABLES = [
    'post_language'               => 'post_id',
    'product_language'            => 'product_id',
    'post_catalogue_language'     => 'post_catalogue_id',
    'product_catalogue_language'  => 'product_catalogue_id',
];

$changed = 0;

foreach (TABLES as $table => $pk) {
    foreach (PLAIN_COLUMNS as $col) {
        if (!Schema::hasColumn($table, $col)) {
            continue;
        }

        $rows = DB::table($table)
            ->where($col, 'like', '%&%;%')
            ->get([$pk, 'language_id', $col]);

        foreach ($rows as $row) {
            $old = (string) ($row->{$col} ?? '');
            if ($old === '') {
                continue;
            }

            // Giai ma lap lai: gia tri da bi ma hoa chong nhieu lop.
            $new = $old;
            for ($i = 0; $i < 10; $i++) {
                $step = html_entity_decode($new, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($step === $new) {
                    break;
                }
                $new = $step;
            }

            if ($new === $old) {
                continue;
            }

            $changed++;
            printf("%-28s %-18s #%s\n", $table, $col, $row->{$pk});
            printf("    cu : %s\n", mb_substr($old, 0, 72));
            printf("    moi: %s\n", mb_substr($new, 0, 72));

            if ($apply) {
                DB::table('seo_content_backup')->insert([
                    'table_name'  => $table,
                    'row_id'      => $row->{$pk},
                    'language_id' => $row->language_id,
                    'column_name' => $col,
                    'old_value'   => $old,
                    'note'        => 'fix-plaintext-entities 30/10/2026',
                    'saved_at'    => now(),
                ]);

                DB::table($table)
                    ->where($pk, $row->{$pk})
                    ->where('language_id', $row->language_id)
                    ->update([$col => $new]);
            }
        }
    }
}

echo "\n" . ($apply ? "DA GHI" : "XEM TRUOC (them --apply de ghi that)") . "\n";
echo "  gia tri bi sua: $changed\n";
