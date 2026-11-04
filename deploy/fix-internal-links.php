<?php
/**
 * Sua lien ket noi bo hong / phai di qua 301, tieu de SEO trung nhau, va link
 * topbar 404. Co sao luu truoc.
 *
 *     php deploy/fix-internal-links.php            # chi xem truoc, KHONG ghi
 *     php deploy/fix-internal-links.php --apply    # ghi that
 *
 * Theo bao cao kiem toan Solann/Antigravity ngay 04/11/2026. Cac cau SQL
 * trong bao cao deu la `UPDATE posts SET content = ...` - bang `posts` khong
 * giu noi dung (no nam o *_language), nen chay chung khong sua duoc gi.
 *
 * ----------------------------------------------------------------------------
 * 1. href noi bo tro vao URL hong hoac URL 301. Moi href tro ve site duoc dua
 *    ve dang chuan https://karaokedep.com.vn/{slug}.html, theo cac quy tac:
 *      http:// , www.                         -> https://karaokedep.com.vn
 *      /index.php/slug.html                   -> /slug.html
 *      slug.html<rac dan nham>                -> slug.html
 *          (vd "am-thanh-karaoke.htmlâm%20thanh%20karaoke")
 *      slug.htm  | slug (thieu duoi)          -> slug.html
 *      slug-a123.html | slug-p184.html        -> slug.html
 *      kien-thuc-phong-thuy.html              -> phong-thuy.html
 *    Chi doi khi slug dich CO trong `routers`. Khong co thi giu nguyen - sua
 *    bua thanh mot link 404 khac con te hon.
 *
 * 2. meta_title trung nhau: 10 bai cung "vat lieu thi cong karaoke an toan
 *    chong chay", 6 bai cung "Thiet Ke Quan Karaoke Nha Cap 4"... Nhom nao co
 *    tu 2 dong tro len cung meta_title (ma ten bai khac nhau) thi xoa
 *    meta_title -> seo() dung ten bai (H1), von da khac nhau. Day chi la
 *    cach cam mau; viec gop/viet lai noi dung la viec cua bien tap.
 *
 * 3. Menu topbar "Kien Thuc Phong Thuy" (menu_language menu_id 33) tro vao
 *    kien-thuc-phong-thuy.html (404). Danh muc that la phong-thuy.html.
 *
 * Moi gia tri cu ghi vao `seo_content_backup` (note 'fix-internal-links').
 * Sau khi --apply phai chay `php artisan cache:clear` (menu duoc cache).
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$apply = in_array('--apply', $argv, true);
const NOTE = 'fix-internal-links 04/11/2026';
const BASE = 'https://karaokedep.com.vn/';

const TARGETS = [
    'post_language'              => ['post_id',              ['description', 'content']],
    'product_language'           => ['product_id',           ['description', 'content']],
    'post_catalogue_language'    => ['post_catalogue_id',    ['description', 'content']],
    'product_catalogue_language' => ['product_catalogue_id', ['description', 'content']],
];

const RENAMED = ['kien-thuc-phong-thuy' => 'phong-thuy'];

if ($apply && !Schema::hasTable('seo_content_backup')) {
    fwrite(STDERR, "Thieu bang seo_content_backup (tao boi fix-content-urls.php)\n");
    exit(1);
}

$routers = array_flip(DB::table('routers')->where('language_id', 1)->pluck('canonical')->all());
$exists = fn (string $slug) => isset($routers[$slug]);

function backup(string $table, $rowId, $lang, string $col, $old): void
{
    DB::table('seo_content_backup')->insert([
        'table_name' => $table, 'row_id' => $rowId, 'language_id' => $lang,
        'column_name' => $col, 'old_value' => $old, 'note' => NOTE, 'saved_at' => now(),
    ]);
}

/** Tra ve URL chuan cho mot href noi bo, hoac null neu khong can / khong the sua. */
function fixHref(string $href, callable $exists): ?string
{
    $raw = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $dec = rawurldecode($raw);
    if ($dec === '' || !preg_match('#^(?:(?:https?:)?//(?:www\.)?karaokedep\.com\.vn)?(/.*)?$#isu', $dec, $m)) {
        return null;
    }
    if (!preg_match('#karaokedep\.com\.vn#i', $dec) && !str_starts_with($dec, '/')) {
        return null;
    }
    // Chi doi tien to http:// hoac www. - dung cho link anh, file, va link khong xac dinh duoc dich.
    $prefixOnly = function () use ($raw) {
        $new = preg_replace('#^(?:https?:)?//(?:www\.)?karaokedep\.com\.vn#i', 'https://karaokedep.com.vn', $raw);
        return $new === $raw ? null : $new;
    };

    $path = preg_replace('#^/index\.php(?=/|$)#i', '', $m[1] ?? '/');
    if ($path === '' || $path === '/') {
        return in_array($raw, ['/', BASE, rtrim(BASE, '/')], true) ? null : BASE;
    }
    if (!preg_match('#^/([a-z0-9-]+)(.*)$#isu', $path, $s)) {
        return $prefixOnly();
    }
    $slug = strtolower($s[1]);
    $rest = $s[2];

    if (preg_match('#^(?:\.html?)?(/trang-\d+\.html)?([?\#].*)?$#i', $rest, $r)) {
        $tail = ($r[1] ?? '') . ($r[2] ?? '');      // phan trang, ?query, #neo: giu lai
    } elseif (preg_match('#^\.html?#i', $rest)) {
        $tail = '';                                 // rac dan nham sau .html -> bo
    } else {
        return $prefixOnly();                       // /uploads/..., /x.jpg, duong dan la
    }

    $candidates = array_filter([RENAMED[$slug] ?? null, $slug, preg_match('#^(.+)-[ap]\d+$#', $slug, $mm) ? $mm[1] : null]);
    foreach ($candidates as $c) {
        if ($exists($c)) {
            $new = BASE . $c . (str_starts_with($tail, '/trang-') ? '' : '.html') . $tail;
            return $new === $raw ? null : $new;
        }
    }
    return $prefixOnly();
}

$stats = ['links' => 0, 'rows' => 0, 'skipped' => []];

foreach (TARGETS as $table => [$pk, $columns]) {
    $rows = DB::table($table)
        ->where(function ($q) use ($columns) {
            foreach ($columns as $c) {
                $q->orWhere($c, 'like', '%karaokedep.com.vn%')->orWhere($c, 'like', '%href="/%');
            }
        })
        ->get(array_merge([$pk, 'language_id'], $columns));

    foreach ($rows as $row) {
        $changed = [];
        foreach ($columns as $c) {
            $old = (string) ($row->{$c} ?? '');
            $new = preg_replace_callback('#(\bhref\s*=\s*)(["\'])(.*?)\2#isu', function ($m) use ($exists, &$stats, $table, $row, $pk) {
                $fixed = fixHref($m[3], $exists);
                if ($fixed === null) {
                    $d = rawurldecode($m[3]);
                    if (preg_match('#^(/|https?://(www\.)?karaokedep)#i', $d)
                        && preg_match('#\.html?#i', $d)
                        && !preg_match('#^(https://karaokedep\.com\.vn)?/[a-z0-9-]+(\.html|/trang-\d+\.html)([?\#].*)?$#i', $d)) {
                        $stats['skipped'][] = "$table#{$row->{$pk}}  {$m[3]}";
                    }
                    return $m[0];
                }
                $stats['links']++;
                echo sprintf("  %-26s #%-6s %s\n      -> %s\n", $table, $row->{$pk}, mb_strimwidth($m[3], 0, 110, '…'), $fixed);
                return $m[1] . $m[2] . $fixed . $m[2];
            }, $old);
            if ($new !== null && $new !== $old) {
                $changed[$c] = $new;
                if ($apply) backup($table, $row->{$pk}, $row->language_id, $c, $old);
            }
        }
        if ($changed) {
            $stats['rows']++;
            if ($apply) {
                DB::table($table)->where($pk, $row->{$pk})->where('language_id', $row->language_id)->update($changed);
            }
        }
    }
}

echo "\n== meta_title trung nhau ==\n";
$titleRows = 0;
foreach (['post_language' => 'post_id', 'product_language' => 'product_id'] as $table => $pk) {
    $dups = DB::table($table)->where('language_id', 1)->where('meta_title', '<>', '')
        ->select(DB::raw('LOWER(TRIM(meta_title)) t'), DB::raw('COUNT(*) n'), DB::raw('COUNT(DISTINCT LOWER(TRIM(name))) dn'))
        ->groupBy('t')->having('n', '>', 1)->having('dn', '>', 1)->pluck('t');
    foreach ($dups as $t) {
        $rows = DB::table($table)->where('language_id', 1)->whereRaw('LOWER(TRIM(meta_title)) = ?', [$t])->get([$pk, 'name', 'meta_title']);
        echo "  [$table] \"{$rows[0]->meta_title}\" x" . count($rows) . "\n";
        foreach ($rows as $r) {
            echo "      #{$r->{$pk}}  -> " . strip_tags($r->name) . "\n";
            $titleRows++;
            if ($apply) {
                backup($table, $r->{$pk}, 1, 'meta_title', $r->meta_title);
                DB::table($table)->where($pk, $r->{$pk})->where('language_id', 1)->update(['meta_title' => '']);
            }
        }
    }
}

echo "\n== menu topbar ==\n";
$menu = DB::table('menu_language')->where('menu_id', 33)->where('language_id', 1)->first();
if ($menu && $menu->canonical === 'kien-thuc-phong-thuy.html' && $exists('phong-thuy')) {
    echo "  menu 33: kien-thuc-phong-thuy.html -> phong-thuy.html\n";
    if ($apply) {
        backup('menu_language', 33, 1, 'canonical', $menu->canonical);
        DB::table('menu_language')->where('menu_id', 33)->where('language_id', 1)->update(['canonical' => 'phong-thuy.html']);
    }
} else {
    echo "  menu 33 da dung hoac khong con (" . ($menu->canonical ?? '-') . ")\n";
}

if ($stats['skipped']) {
    echo "\n== Khong tu sua duoc (dich khong ton tai) ==\n";
    foreach (array_unique($stats['skipped']) as $s) echo "  $s\n";
}

echo "\n" . ($apply ? "DA GHI - nho chay: php artisan cache:clear\n" : "XEM TRUOC (them --apply de ghi that)\n");
echo sprintf("  href da sua      : %d (trong %d dong)\n", $stats['links'], $stats['rows']);
echo sprintf("  meta_title da xoa: %d\n", $titleRows);
