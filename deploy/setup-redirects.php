<?php
/**
 * Cai dat tinh nang "QL Chuyen Huong" (chuyen huong 301 tu admin).
 *
 *     php deploy/setup-redirects.php
 *
 * Chay MOT lan sau khi git pull ban co tinh nang nay. Chay lai bao nhieu lan
 * cung an toan: chi tao/them cai con thieu, khong xoa gi.
 *
 *   1. Tao bang `redirects` neu chua co.
 *   2. Them 4 quyen redirect.* vao bang `permissions` va gan cho moi nhom
 *      thanh vien dang co quyen contact.index (tuc nhom quan tri).
 *   3. Chep cac duong dan cu dang viet cung trong RouterController::RENAMED
 *      vao bang de admin nhin thay va sua duoc.
 *
 * Khong dung `php artisan migrate`: tren production bang `migrations` khong
 * khop lich su that, migrate co the chay lai migration cu.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('redirects')) {
    Schema::create('redirects', function (Blueprint $t) {
        $t->bigIncrements('id');
        $t->string('source_path', 500);
        $t->string('target_url', 1000);
        $t->unsignedSmallInteger('status_code')->default(301);
        $t->tinyInteger('publish')->default(2);
        $t->string('note', 255)->nullable();
        $t->unsignedInteger('hits')->default(0);
        $t->timestamp('last_hit_at')->nullable();
        $t->timestamps();
        $t->unique('source_path', 'redirects_source_path_unique');
    });
    echo "[1] Da tao bang redirects\n";
} else {
    echo "[1] Bang redirects da co\n";
}

$now = now();
$perms = [
    'redirect.index'   => 'Xem danh sách chuyển hướng',
    'redirect.create'  => 'Tạo mới chuyển hướng',
    'redirect.edit'    => 'Cập nhật chuyển hướng',
    'redirect.destroy' => 'Xóa chuyển hướng',
];

$adminGroups = DB::table('user_catalogue_permission as ucp')
    ->join('permissions as p', 'p.id', '=', 'ucp.permission_id')
    ->where('p.canonical', 'contact.index')
    ->pluck('ucp.user_catalogue_id')
    ->unique();

foreach ($perms as $canonical => $name) {
    $id = DB::table('permissions')->where('canonical', $canonical)->value('id');
    if (!$id) {
        $id = DB::table('permissions')->insertGetId([
            'name' => $name, 'canonical' => $canonical,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }
    foreach ($adminGroups as $groupId) {
        $exists = DB::table('user_catalogue_permission')
            ->where(['user_catalogue_id' => $groupId, 'permission_id' => $id])
            ->exists();
        if (!$exists) {
            DB::table('user_catalogue_permission')->insert([
                'user_catalogue_id' => $groupId, 'permission_id' => $id,
            ]);
        }
    }
}
echo "[2] Quyen redirect.* da gan cho nhom: " . $adminGroups->implode(', ') . "\n";

$suffix = config('apps.general.suffix');
$seed = [
    'kien-thuc-phong-thuy' => ['phong-thuy', 'Menu 33 đổi tên 04/11/2026'],
];
foreach ($seed as $old => [$new, $note]) {
    $src = '/' . $old . $suffix;
    if (!DB::table('redirects')->where('source_path', $src)->exists()) {
        DB::table('redirects')->insert([
            'source_path' => $src, 'target_url' => '/' . $new . $suffix,
            'status_code' => 301, 'publish' => 2, 'note' => $note,
            'hits' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
echo "[3] Da chep duong dan cu vao bang\n";

Cache::forget('redirects.map.v1');
echo "XONG. Vao admin: /redirect/index\n";
