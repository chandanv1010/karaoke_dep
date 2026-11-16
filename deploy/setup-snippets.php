<?php
/**
 * Cai dat tinh nang "Vi Tri Chen Ma" (ma nhung theo vi tri, tu admin).
 *
 *     php deploy/setup-snippets.php
 *
 * Chay lai bao nhieu lan cung an toan: chi tao/them cai con thieu.
 *   1. Tao bang `code_snippets`.
 *   2. Them 4 quyen snippet.* va gan cho cac nhom dang co quyen contact.index.
 *
 * Khong dung `php artisan migrate` (xem setup-redirects.php).
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('code_snippets')) {
    Schema::create('code_snippets', function (Blueprint $t) {
        $t->bigIncrements('id');
        $t->string('name', 191);
        $t->string('position', 64)->index();
        $t->longText('code');
        $t->string('pages', 255)->nullable();   // rong = moi loai trang
        $t->text('urls')->nullable();            // rong = moi URL
        $t->tinyInteger('publish')->default(2);
        $t->unsignedSmallInteger('sort')->default(0);
        $t->string('note', 255)->nullable();
        $t->timestamps();
    });
    echo "[1] Da tao bang code_snippets\n";
} else {
    echo "[1] Bang code_snippets da co\n";
}

$now = now();
$perms = [
    'snippet.index'   => 'Xem danh sách mã nhúng',
    'snippet.create'  => 'Tạo mới mã nhúng',
    'snippet.edit'    => 'Cập nhật mã nhúng',
    'snippet.destroy' => 'Xóa mã nhúng',
];

$adminGroups = DB::table('user_catalogue_permission as ucp')
    ->join('permissions as p', 'p.id', '=', 'ucp.permission_id')
    ->where('p.canonical', 'contact.index')
    ->pluck('ucp.user_catalogue_id')
    ->unique();

foreach ($perms as $canonical => $name) {
    $id = DB::table('permissions')->where('canonical', $canonical)->value('id')
        ?: DB::table('permissions')->insertGetId([
            'name' => $name, 'canonical' => $canonical, 'created_at' => $now, 'updated_at' => $now,
        ]);
    foreach ($adminGroups as $groupId) {
        $pair = ['user_catalogue_id' => $groupId, 'permission_id' => $id];
        if (!DB::table('user_catalogue_permission')->where($pair)->exists()) {
            DB::table('user_catalogue_permission')->insert($pair);
        }
    }
}
echo "[2] Quyen snippet.* da gan cho nhom: " . $adminGroups->implode(', ') . "\n";

Cache::forget('code_snippets.v1');
echo "XONG. Vao admin: /snippet/index\n";
