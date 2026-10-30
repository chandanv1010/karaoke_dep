<?php
/**
 * Sua cac canonical bi dan nham nguyen URL vao o nhap.
 *
 *     php deploy/fix-broken-canonicals.php
 *     php deploy/fix-broken-canonicals.php --apply
 *
 * VAN DE
 * Bon bai viet co nguoi nhap dan ca duong dan day du vao o canonical. Bo tao
 * slug cua CMS cat sach dau ':' '/' '.' nen gia tri luu lai thanh:
 *
 *     httpskaraokedepcomvn
 *     httpskaraokedepcomvnphong-karaokehtml
 *     httpskaraokedepcomvnsan-pham-ktvhtml
 *     httpskaraokedepcomvnthiet-ke-phong-karaokehtml
 *
 * Hau qua: bon bai do dang song o dia chi rac, VA khoi JSON-LD cua trang danh
 * muc phat ra chin URL kieu
 *     https://karaokedep.com.vn/httpskaraokedepcomvnsan-pham-ktvhtml.html
 * - dung cai ma bao cao kiem toan goi la "loi ghep chuoi Schema tao URL rac".
 * Goc re khong nam o ham sinh schema (ham do dung), ma o du lieu.
 *
 * CACH SUA
 * Dat lai canonical bang slug sinh tu chinh tieu de bai viet, kiem tra khong
 * trung voi canonical nao dang co. Phai sua CA HAI bang:
 *   - `routers`        : bang ma RouterController dung de phan giai URL
 *   - `post_language`  : bang ma cac trang khac doc de dung lien ket
 * Sua mot bang thi bai viet se khong mo duoc.
 *
 * SAU KHI DOI
 * Dia chi cu se tra 404. Chung von la URL rac, gan nhu khong co lien ket nao
 * tro vao, nen de 404 la dung. Bang `seo_canonical_redirect` duoc ghi kem de
 * sau nay muon dat 301 cu -> moi thi co san du lieu.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;

$apply = in_array('--apply', $argv, true);

$broken = DB::table('post_language')
    ->where('canonical', 'like', 'http%')
    ->get(['post_id', 'language_id', 'canonical', 'name']);

if ($broken->isEmpty()) {
    echo "Khong con canonical nao hong.\n";
    exit(0);
}

if ($apply && !Schema::hasTable('seo_canonical_redirect')) {
    Schema::create('seo_canonical_redirect', function (Blueprint $t) {
        $t->bigIncrements('id');
        $t->string('old_canonical', 255)->index();
        $t->string('new_canonical', 255);
        $t->unsignedBigInteger('post_id');
        $t->timestamp('saved_at')->nullable();
    });
    echo "Da tao bang seo_canonical_redirect\n";
}

$taken = DB::table('routers')->pluck('canonical')->map(fn ($c) => strtolower((string) $c))->flip();

$plan = [];
foreach ($broken as $row) {
    $slug = Str::slug((string) $row->name, '-');

    if ($slug === '') {
        $slug = 'bai-viet-' . $row->post_id;
    }
    // Khong de dai qua - URL tren 115 ky tu bi bao cao danh dau la qua dai.
    if (strlen($slug) > 100) {
        $slug = rtrim(substr($slug, 0, 100), '-');
    }

    $candidate = $slug;
    $i = 2;
    while (isset($taken[strtolower($candidate)])) {
        $candidate = $slug . '-' . $i++;
    }
    $taken[strtolower($candidate)] = true;

    $plan[] = [
        'post_id'   => $row->post_id,
        'language_id' => $row->language_id,
        'old'       => $row->canonical,
        'new'       => $candidate,
        'name'      => $row->name,
    ];
}

foreach ($plan as $p) {
    printf("post #%-5s %s\n", $p['post_id'], mb_substr($p['name'], 0, 66));
    printf("   cu : /%s.html\n", $p['old']);
    printf("   moi: /%s.html\n", $p['new']);
}

if ($apply) {
    foreach ($plan as $p) {
        DB::table('seo_canonical_redirect')->insert([
            'old_canonical' => $p['old'],
            'new_canonical' => $p['new'],
            'post_id'       => $p['post_id'],
            'saved_at'      => now(),
        ]);

        DB::table('post_language')
            ->where('post_id', $p['post_id'])
            ->where('language_id', $p['language_id'])
            ->update(['canonical' => $p['new']]);

        DB::table('routers')
            ->where('module_id', $p['post_id'])
            ->where('canonical', $p['old'])
            ->update(['canonical' => $p['new']]);
    }
}

echo "\n" . ($apply ? "DA GHI" : "XEM TRUOC (them --apply de ghi that)") . "\n";
echo "  so bai sua: " . count($plan) . "\n";
