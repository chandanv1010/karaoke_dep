<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Tạo mới attribute', 'canonical' => 'attribute.catalogue.create'],
            ['name' => 'Xóa attribute', 'canonical' => 'attribute.catalogue.destroy'],
            ['name' => 'Xem danh sách attribute', 'canonical' => 'attribute.catalogue.index'],
            ['name' => 'Cập nhật attribute', 'canonical' => 'attribute.catalogue.update'],
            ['name' => 'Tạo mới attribute', 'canonical' => 'attribute.create'],
            ['name' => 'Xóa attribute', 'canonical' => 'attribute.destroy'],
            ['name' => 'Xem danh sách attribute', 'canonical' => 'attribute.index'],
            ['name' => 'Cập nhật attribute', 'canonical' => 'attribute.update'],
            ['name' => 'Xóa contact', 'canonical' => 'contact.destroy'],
            ['name' => 'Xem danh sách contact', 'canonical' => 'contact.index'],
            ['name' => 'Tạo mới customer', 'canonical' => 'customer.catalogue.create'],
            ['name' => 'Xóa customer', 'canonical' => 'customer.catalogue.destroy'],
            ['name' => 'Xem danh sách customer', 'canonical' => 'customer.catalogue.index'],
            ['name' => 'Cập nhật customer', 'canonical' => 'customer.catalogue.update'],
            ['name' => 'Tạo mới customer', 'canonical' => 'customer.create'],
            ['name' => 'Xóa customer', 'canonical' => 'customer.destroy'],
            ['name' => 'Xem danh sách customer', 'canonical' => 'customer.index'],
            ['name' => 'Cập nhật customer', 'canonical' => 'customer.update'],
            ['name' => 'Tạo mới language', 'canonical' => 'language.create'],
            ['name' => 'Xóa language', 'canonical' => 'language.destroy'],
            ['name' => 'Xem danh sách language', 'canonical' => 'language.index'],
            ['name' => 'Dịch thuật language', 'canonical' => 'language.translate'],
            ['name' => 'Cập nhật language', 'canonical' => 'language.update'],
            ['name' => 'Tạo mới lecturer', 'canonical' => 'lecturer.create'],
            ['name' => 'Xóa lecturer', 'canonical' => 'lecturer.destroy'],
            ['name' => 'Xem danh sách lecturer', 'canonical' => 'lecturer.index'],
            ['name' => 'Cập nhật lecturer', 'canonical' => 'lecturer.update'],
            ['name' => 'Tạo mới menu', 'canonical' => 'menu.create'],
            ['name' => 'Xóa menu', 'canonical' => 'menu.destroy'],
            ['name' => 'Xem danh sách menu', 'canonical' => 'menu.index'],
            ['name' => 'Cập nhật menu', 'canonical' => 'menu.update'],
            ['name' => 'Xem danh sách order', 'canonical' => 'order.index'],
            ['name' => 'Tạo mới permission', 'canonical' => 'permission.create'],
            ['name' => 'Xóa permission', 'canonical' => 'permission.destroy'],
            ['name' => 'Xem danh sách permission', 'canonical' => 'permission.index'],
            ['name' => 'Cập nhật permission', 'canonical' => 'permission.update'],
            ['name' => 'Tạo mới post', 'canonical' => 'post.catalogue.create'],
            ['name' => 'Xóa post', 'canonical' => 'post.catalogue.destroy'],
            ['name' => 'Xem danh sách post', 'canonical' => 'post.catalogue.index'],
            ['name' => 'Cập nhật post', 'canonical' => 'post.catalogue.update'],
            ['name' => 'Tạo mới post', 'canonical' => 'post.create'],
            ['name' => 'Xóa post', 'canonical' => 'post.destroy'],
            ['name' => 'Xem danh sách post', 'canonical' => 'post.index'],
            ['name' => 'Cập nhật post', 'canonical' => 'post.update'],
            ['name' => 'Tạo mới product', 'canonical' => 'product.catalogue.create'],
            ['name' => 'Xóa product', 'canonical' => 'product.catalogue.destroy'],
            ['name' => 'Xem danh sách product', 'canonical' => 'product.catalogue.index'],
            ['name' => 'Cập nhật product', 'canonical' => 'product.catalogue.update'],
            ['name' => 'Tạo mới product', 'canonical' => 'product.create'],
            ['name' => 'Xóa product', 'canonical' => 'product.destroy'],
            ['name' => 'Xem danh sách product', 'canonical' => 'product.index'],
            ['name' => 'Cập nhật product', 'canonical' => 'product.update'],
            ['name' => 'Tạo mới promotion', 'canonical' => 'promotion.create'],
            ['name' => 'Xóa promotion', 'canonical' => 'promotion.destroy'],
            ['name' => 'Xem danh sách promotion', 'canonical' => 'promotion.index'],
            ['name' => 'Dịch thuật promotion', 'canonical' => 'promotion.translate'],
            ['name' => 'Cập nhật promotion', 'canonical' => 'promotion.update'],
            ['name' => 'Xóa review', 'canonical' => 'review.destroy'],
            ['name' => 'Xem danh sách review', 'canonical' => 'review.index'],
            ['name' => 'Tạo mới slide', 'canonical' => 'slide.create'],
            ['name' => 'Xóa slide', 'canonical' => 'slide.destroy'],
            ['name' => 'Cập nhật slide', 'canonical' => 'slide.edit'],
            ['name' => 'Xem danh sách slide', 'canonical' => 'slide.index'],
            ['name' => 'Tạo mới source', 'canonical' => 'source.create'],
            ['name' => 'Xóa source', 'canonical' => 'source.destroy'],
            ['name' => 'Xem danh sách source', 'canonical' => 'source.index'],
            ['name' => 'Cập nhật source', 'canonical' => 'source.update'],
            ['name' => 'Tạo mới user', 'canonical' => 'user.catalogue.create'],
            ['name' => 'Xóa user', 'canonical' => 'user.catalogue.destroy'],
            ['name' => 'Xem danh sách user', 'canonical' => 'user.catalogue.index'],
            ['name' => 'Phân quyền user', 'canonical' => 'user.catalogue.permission'],
            ['name' => 'Cập nhật user', 'canonical' => 'user.catalogue.update'],
            ['name' => 'Tạo mới user', 'canonical' => 'user.create'],
            ['name' => 'Xóa user', 'canonical' => 'user.destroy'],
            ['name' => 'Xem danh sách user', 'canonical' => 'user.index'],
            ['name' => 'Cập nhật user', 'canonical' => 'user.update'],
            ['name' => 'Tạo mới voucher', 'canonical' => 'voucher.create'],
            ['name' => 'Xóa voucher', 'canonical' => 'voucher.destroy'],
            ['name' => 'Xem danh sách voucher', 'canonical' => 'voucher.index'],
            ['name' => 'Cập nhật voucher', 'canonical' => 'voucher.update'],
            ['name' => 'Tạo mới widget', 'canonical' => 'widget.create'],
            ['name' => 'Xóa widget', 'canonical' => 'widget.destroy'],
            ['name' => 'Xem danh sách widget', 'canonical' => 'widget.index'],
            ['name' => 'Dịch thuật widget', 'canonical' => 'widget.translate'],
            ['name' => 'Cập nhật widget', 'canonical' => 'widget.update'],
        ];

        $insertedIds = [];
        foreach ($permissions as $permData) {
            $perm = Permission::updateOrCreate(
                ['canonical' => $permData['canonical']],
                ['name' => $permData['name']]
            );
            $insertedIds[] = $perm->id;
        }

        // Sync for admin group (ID: 3) and legacy administrators (ID: 2)
        $adminCatalogues = [2, 3];
        foreach ($adminCatalogues as $catId) {
            $catalogueExists = DB::table('user_catalogues')->where('id', $catId)->exists();
            if ($catalogueExists) {
                DB::table('user_catalogue_permission')->where('user_catalogue_id', $catId)->delete();
                $pivotData = [];
                foreach ($insertedIds as $pid) {
                    $pivotData[] = [
                        'user_catalogue_id' => $catId,
                        'permission_id' => $pid
                    ];
                }
                DB::table('user_catalogue_permission')->insert($pivotData);
            }
        }

        // Also activate default admin account if not already activated
        DB::table('users')->where('email', 'admin@example.com')->update(['publish' => 2]);
    }
}
