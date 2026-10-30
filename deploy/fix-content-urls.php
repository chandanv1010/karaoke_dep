<?php
/**
 * Sua hai loi noi dung trong CSDL, co sao luu truoc.
 *
 *     php deploy/fix-content-urls.php            # chi xem truoc, KHONG ghi
 *     php deploy/fix-content-urls.php --apply    # ghi that
 *
 * ----------------------------------------------------------------------------
 * LOI 1: lien ket noi bo con dung http://
 *
 * Bao cao kiem toan ngay 29/10/2026 dem duoc 4.289 lien ket noi bo mang giao
 * thuc http:// tro ve 181 URL. Moi lan nguoi dung bam vao la mot vong chuyen
 * huong 301 thua, va PageRank ro ri qua moi vong do.
 *
 * Bao cao de xuat cau SQL nay:
 *     UPDATE posts SET content = REPLACE(content, 'http://...', 'https://...');
 * Chay cau do se KHONG co tac dung gi: bang `posts` khong giu noi dung. Noi
 * dung nam o `post_language` (va ba bang *_language tuong ung). Dem that:
 *     post_language 344 dong | product_language 70 | post_catalogue 12 |
 *     product_catalogue 3  => 429 dong.
 *
 * ----------------------------------------------------------------------------
 * LOI 2: dau & bi ma hoa chong nhieu lop
 *
 * Tieu de bai "Song Gio" luu trong CSDL la
 *     Jack &amp;amp;amp;amp;amp;amp; KICM
 * View escape them mot lop nua khi render, nen nguoi doc nhin thay nguyen cum
 * "&amp;amp;amp;..." tren trang, tren the <title> va trong ket qua tim kiem.
 * Moi lan luu lai trong CMS lai chong them mot lop.
 *
 * Cach sua: thay '&amp;amp;' bang '&amp;' lap lai cho den khi het. Ket qua
 * cuoi cung la '&amp;', dung mot lop - render ra dung mot dau '&'.
 * KHONG dung html_entity_decode toan bo: lam the se giai ma luon ca
 * '&lt;' '&gt;' trong noi dung va pha vo ma HTML.
 *
 * ----------------------------------------------------------------------------
 * SAO LUU
 * Moi gia tri cu duoc ghi vao bang `seo_content_backup` truoc khi sua. Muon
 * tra lai thi doc bang do ra va ghi nguoc.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$apply = in_array('--apply', $argv, true);

/** bang => [cot khoa chinh, cac cot noi dung can quet] */
const TARGETS = [
    'post_language'               => ['post_id',               ['name', 'description', 'content', 'meta_title', 'meta_description']],
    'product_language'            => ['product_id',            ['name', 'description', 'content', 'meta_title', 'meta_description']],
    'post_catalogue_language'     => ['post_catalogue_id',     ['name', 'description', 'content', 'meta_title', 'meta_description']],
    'product_catalogue_language'  => ['product_catalogue_id',  ['name', 'description', 'content', 'meta_title', 'meta_description']],
];

const HTTP_FROM = 'http://karaokedep.com.vn';
const HTTP_TO   = 'https://karaokedep.com.vn';

function collapseAmp(string $v): string
{
    $guard = 0;
    while (str_contains($v, '&amp;amp;') && $guard++ < 50) {
        $v = str_replace('&amp;amp;', '&amp;', $v);
    }
    return $v;
}

if ($apply && !Schema::hasTable('seo_content_backup')) {
    Schema::create('seo_content_backup', function (Blueprint $t) {
        $t->bigIncrements('id');
        $t->string('table_name', 64);
        $t->unsignedBigInteger('row_id');
        $t->unsignedInteger('language_id');
        $t->string('column_name', 64);
        $t->longText('old_value')->nullable();
        $t->string('note', 64)->nullable();
        $t->timestamp('saved_at')->nullable();
    });
    echo "Da tao bang seo_content_backup\n";
}

$totals = ['http' => 0, 'amp' => 0, 'rows' => 0];

foreach (TARGETS as $table => [$pk, $columns]) {
    $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
    if (empty($columns)) {
        continue;
    }

    $rows = DB::table($table)
        ->where(function ($q) use ($columns) {
            foreach ($columns as $c) {
                $q->orWhere($c, 'like', '%' . HTTP_FROM . '%')
                  ->orWhere($c, 'like', '%&amp;amp;%');
            }
        })
        ->get(array_merge([$pk, 'language_id'], $columns));

    foreach ($rows as $row) {
        $changedCols = [];

        foreach ($columns as $c) {
            $old = (string) ($row->{$c} ?? '');
            if ($old === '') {
                continue;
            }

            $new = str_replace(HTTP_FROM, HTTP_TO, $old);
            $nHttp = substr_count($old, HTTP_FROM);

            $before = $new;
            $new = collapseAmp($new);
            $nAmp = ($before !== $new) ? 1 : 0;

            if ($new === $old) {
                continue;
            }

            $changedCols[$c] = $new;
            $totals['http'] += $nHttp;
            $totals['amp']  += $nAmp;

            if ($apply) {
                DB::table('seo_content_backup')->insert([
                    'table_name'  => $table,
                    'row_id'      => $row->{$pk},
                    'language_id' => $row->language_id,
                    'column_name' => $c,
                    'old_value'   => $old,
                    'note'        => 'fix-content-urls 30/10/2026',
                    'saved_at'    => now(),
                ]);
            }
        }

        if (empty($changedCols)) {
            continue;
        }

        $totals['rows']++;

        if ($apply) {
            DB::table($table)
                ->where($pk, $row->{$pk})
                ->where('language_id', $row->language_id)
                ->update($changedCols);
        }
    }

    echo sprintf("%-28s quet %d dong\n", $table, count($rows));
}

echo "\n";
echo $apply ? "DA GHI\n" : "XEM TRUOC (them --apply de ghi that)\n";
echo sprintf("  dong bi sua          : %d\n", $totals['rows']);
echo sprintf("  lien ket http-> https: %d\n", $totals['http']);
echo sprintf("  cho &amp;amp; gom lai: %d\n", $totals['amp']);
