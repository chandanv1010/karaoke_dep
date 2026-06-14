<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KaraokeHomepageSeeder extends Seeder
{
    private int $languageId = 1;
    private int $userId = 1;

    public function run(): void
    {
        $now = now();
        $this->userId = (int) (DB::table('users')->value('id') ?? 1);

        DB::table('slides')->updateOrInsert(
            ['keyword' => 'main-slide'],
            [
                'name' => 'Hero karaoke',
                'description' => 'Homepage hero',
                'item' => json_encode([
                    1 => [
                        [
                            'image' => '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg',
                            'name' => 'Thiết kế & Thi Công',
                            'description' => "Từ ý tưởng, thiết kế 3D, cách âm, nội thất, âm thanh ánh sáng.\nChúng tôi giúp bạn tạo nên phòng karaoke đẳng cấp, tối ưu chi phí và vận hành hiệu quả.",
                            'canonical' => '#',
                            'alt' => 'Phòng Karaoke Chuyên Nghiệp',
                            'window' => '',
                        ],
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'setting' => json_encode([
                    'actions' => [
                        ['label' => 'Nhận báo giá miễn phí', 'url' => '#'],
                        ['label' => 'Xem các mẫu phòng', 'url' => '#'],
                    ],
                    'stats' => [
                        ['value' => '10+', 'label' => 'Năm kinh nghiệm'],
                        ['value' => '34+', 'label' => 'Tỉnh thành'],
                        ['value' => '10+', 'label' => 'Quốc gia'],
                        ['value' => '500+', 'label' => 'Dự án hoàn thành'],
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'short_code' => '',
                'publish' => 2,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('widgets')->updateOrInsert(
            ['keyword' => 'intro'],
            [
                'name' => 'Giới thiệu AZKTV',
                'description' => json_encode([
                    $this->languageId => 'Chúng tôi cung cấp giải pháp thiết kế và thi công phòng karaoke trọn gói, từ tư vấn ý tưởng, thiết kế concept 3D đến thi công nội thất và lắp đặt hệ thống âm thanh - ánh sáng hoàn chỉnh. Với kinh nghiệm thực hiện hàng trăm phòng karaoke trên toàn quốc, đội ngũ của chúng tôi hiểu rõ cách tạo nên một không gian giải trí vừa đẹp, vừa hiệu quả trong vận hành kinh doanh.',
                ], JSON_UNESCAPED_UNICODE),
                'album' => json_encode([
                    '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg',
                    '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg',
                ], JSON_UNESCAPED_UNICODE),
                'model_id' => json_encode([]),
                'model' => 'Post',
                'short_code' => 'Chuyên gia thiết kế phòng karaoke',
                'publish' => 2,
                'note' => 'Homepage intro section',
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $constructionIds = $this->seedPosts([
            ['Luxury room', 'Mẫu phòng karaoke thi công theo concept sang trọng.', '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg'],
            ['Cyber room', 'Không gian karaoke ánh sáng hiện đại, tối ưu trải nghiệm.', '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg'],
            ['VIP karaoke', 'Phòng VIP hoàn thiện đồng bộ nội thất, âm thanh và ánh sáng.', '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg'],
            ['VIP karaoke neon', 'Thi công phòng hát hiệu ứng neon nổi bật.', '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg'],
            ['Luxury room classic', 'Mẫu karaoke phong cách luxury phù hợp kinh doanh.', '/uploads/images/thiet-ke/thiet-ke-phong-ngu-01.jpg'],
            ['Cyber room pink', 'Không gian phòng hát trẻ trung với hệ đèn line.', '/uploads/images/thiet-ke/thiet-ke-biet-thu-01.jpg'],
            ['VIP karaoke galaxy', 'Trần sao và màn hình led tạo điểm nhấn cho phòng hát.', '/uploads/images/thiet-ke/thiet-ke-phong-lam-viec-01.jpg'],
            ['VIP karaoke lounge', 'Phòng karaoke kết hợp lounge vận hành hiệu quả.', '/uploads/images/thiet-ke/thiet-ke-phong-bep-01.jpg'],
        ]);

        $productIds = [277, 211, 136, 133, 128, 94, 85, 70];
        foreach ($productIds as $index => $productId) {
            DB::table('products')->where('id', $productId)->update(['order' => 1000 - $index]);
        }
        $designConsultingCatalogueId = 8;
        $designKaraokeCatalogueId = 23;
        $newsCatalogueIds = [
            'news' => 33,
            'experience' => 34,
            'faq' => 32,
        ];
        $designPostIds = [220, 293, 461, 252, 707, 703];
        $newsPostIds = [
            'news' => [708, 707, 701, 692, 684, 683, 679, 677],
            'experience' => [707, 692, 684, 683, 679, 677, 676, 675],
            'faq' => [639, 630, 629, 627, 626, 625, 603, 590],
        ];

        $this->prioritizePostsInCatalogue($designConsultingCatalogueId, $designPostIds);
        $this->prioritizePostsInCatalogue($designKaraokeCatalogueId, $designPostIds);
        foreach ($newsPostIds as $key => $ids) {
            $this->prioritizePostsInCatalogue($newsCatalogueIds[$key], $ids);
        }

        $this->upsertWidget('karaoke-construction', 'Thi công karaoke', 'Post', [706, 704, 703, 702, 701, 700, 699, 698], '', 'Homepage construction module section');
        DB::table('widgets')->where('keyword', 'karaoke-construction')->update([
            'album' => json_encode(['/userfiles/image/home/karaoke-section-bg.png'], JSON_UNESCAPED_UNICODE),
        ]);

        $this->upsertWidget('featured-products', 'Sản phẩm nổi bật', 'Product', $productIds, 'Xem chi tiết', 'Homepage featured product module section');
        DB::table('widgets')->where('keyword', 'featured-products')->update([
            'short_code' => 'Xem thêm',
            'note' => '#',
        ]);

        $this->upsertWidget('home-designs-1', 'Tư vấn thiết kế', 'PostCatalogue', [$designConsultingCatalogueId], '6', 'Homepage design consulting tab');
        $this->upsertWidget('home-designs-2', 'Thiết kế karaoke', 'PostCatalogue', [$designKaraokeCatalogueId], '6', 'Homepage karaoke design tab');
        DB::table('widgets')->whereIn('keyword', ['home-designs-1', 'home-designs-2'])->update([
            'short_code' => 'Xem thêm',
            'note' => '#',
        ]);

        $this->upsertWidget('home-news-1', 'Tin tức KTV', 'PostCatalogue', [$newsCatalogueIds['news']], '7', 'Homepage news column');
        $this->upsertWidget('home-news-2', 'Kinh nghiệm thi công', 'PostCatalogue', [$newsCatalogueIds['experience']], '7', 'Homepage experience column');
        $this->upsertWidget('home-news-3', 'Chuyên đề hỏi đáp', 'PostCatalogue', [$newsCatalogueIds['faq']], '7', 'Homepage FAQ column');
        DB::table('widgets')
            ->whereIn('keyword', ['home-designs', 'home-news'])
            ->update([
                'description' => json_encode([$this->languageId => ''], JSON_UNESCAPED_UNICODE),
                'deleted_at' => $now,
                'updated_at' => $now,
            ]);

        $aboutText = 'Chúng tôi cung cấp giải pháp thiết kế và thi công phòng karaoke trọn gói, từ tư vấn ý tưởng, thiết kế concept 3D đến thi công nội thất và lắp đặt hệ thống âm thanh - ánh sáng hoàn chỉnh. Với kinh nghiệm thực hiện hàng trăm phòng karaoke trên toàn quốc, đội ngũ của chúng tôi hiểu rõ cách tạo nên một không gian giải trí vừa đẹp, vừa hiệu quả trong vận hành kinh doanh.';
        $this->seedIntroduce($aboutText);

        $this->upsertWidget('about-hero', 'Giới thiệu', 'Post', [], '', 'About hero section');
        DB::table('widgets')->where('keyword', 'about-hero')->update([
            'album' => json_encode(['/userfiles/image/bg-about-hero.png'], JSON_UNESCAPED_UNICODE),
            'short_code' => '',
        ]);

        $this->upsertWidget('about-intro', 'AZKTV Việt Nam', 'StaticSection', [], $aboutText, 'Chuyên gia thiết kế phòng karaoke');
        DB::table('widgets')->where('keyword', 'about-intro')->update([
            'short_code' => 'Chuyên gia thiết kế phòng karaoke',
            'album' => json_encode([
                '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg',
                '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $aboutFeatureIds = $this->seedPosts([
            ['Kinh nghiệm thực tế', 'Đội ngũ đã triển khai nhiều mô hình phòng karaoke thực tế.', '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg'],
            ['Thi công chuẩn kỹ thuật', 'Quy trình thi công bám sát tiêu chuẩn âm học và vận hành.', '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg'],
            ['Thiết kế sáng tạo', 'Concept thiết kế riêng theo mô hình kinh doanh và ngân sách.', '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg'],
            ['Chi phí tối ưu', 'Tối ưu vật liệu, thiết bị và ngân sách đầu tư thực tế.', '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg'],
        ]);
        $this->updatePostPresentation($aboutFeatureIds[0], 'Kinh nghiệm thực tế', 'fa fa-trophy');
        $this->updatePostPresentation($aboutFeatureIds[1], 'Thi công chuẩn kỹ thuật', 'fa fa-cogs');
        $this->updatePostPresentation($aboutFeatureIds[2], 'Thiết kế sáng tạo', 'fa fa-pencil-square-o');
        $this->updatePostPresentation($aboutFeatureIds[3], 'Chi phí tối ưu', 'fa fa-line-chart');
        $this->upsertWidget('intro-features', 'Điểm mạnh giới thiệu trang chủ', 'Post', $aboutFeatureIds, '', 'Homepage intro feature records');
        $this->upsertWidget('about-intro-features', 'Điểm mạnh giới thiệu', 'Post', $aboutFeatureIds, '', 'About intro feature records');

        $serviceIds = $this->seedPosts([
            ['Thiết kế 3D', 'Lên concept 3D rõ ràng để chốt phương án trước khi thi công.', '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg'],
            ['Thi công cách âm', 'Xử lý cách âm đúng kỹ thuật cho phòng hát vận hành ổn định.', '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg'],
            ['Lắp đặt âm thanh', 'Tư vấn và lắp đặt hệ thống âm thanh phù hợp mô hình sử dụng.', '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg'],
            ['Ánh sáng & nội thất', 'Hoàn thiện ánh sáng, nội thất và điểm nhấn nhận diện phòng hát.', '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg'],
        ]);
        $this->updatePostPresentation($serviceIds[0], 'Thiết kế 3D', 'fa fa-cube');
        $this->updatePostPresentation($serviceIds[1], 'Thi công cách âm', 'fa fa-volume-up');
        $this->updatePostPresentation($serviceIds[2], 'Lắp đặt âm thanh', 'fa fa-bullseye');
        $this->updatePostPresentation($serviceIds[3], 'Ánh sáng & nội thất', 'fa fa-object-group');
        $this->upsertWidget('intro-services', 'Dịch vụ giới thiệu trang chủ', 'Post', $serviceIds, '', 'Homepage intro service records');
        $this->upsertWidget('about-services', 'Dịch vụ của chúng tôi', 'Post', $serviceIds, $aboutText, 'About service records');
        DB::table('widgets')->where('keyword', 'about-services')->update([
            'album' => json_encode(['/userfiles/image/home/karaoke-section-bg.png'], JSON_UNESCAPED_UNICODE),
        ]);

        $introActionIds = $this->seedPosts([
            ['Xem thêm', 'Link hành động phần giới thiệu.', '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg'],
        ]);
        $this->updatePostPresentation($introActionIds[0], 'Xem thêm', '');
        $this->upsertWidget('intro-action', 'Nút giới thiệu trang chủ', 'Post', $introActionIds, '', 'Homepage intro action record');

        $aboutStatIds = $this->seedPosts([
            ['10+ năm kinh nghiệm', 'Năm kinh nghiệm', '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg'],
            ['34+ tỉnh thành', 'Tỉnh thành', '/uploads/images/thiet-ke/thiet-ke-phong-hop-01.jpg'],
            ['10+ quốc gia', 'Quốc gia', '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg'],
            ['500+ dự án hoàn thành', 'Dự án hoàn thành', '/uploads/images/thiet-ke/thiet-ke-nha-hang-01.jpg'],
        ]);
        $this->updatePostPresentation($aboutStatIds[0], '10+', '');
        $this->updatePostPresentation($aboutStatIds[1], '34+', '');
        $this->updatePostPresentation($aboutStatIds[2], '10+', '');
        $this->updatePostPresentation($aboutStatIds[3], '500+', '');
        $this->upsertWidget('about-stats', 'Số liệu giới thiệu', 'Post', $aboutStatIds, '', 'About intro statistic records');

        $this->fillPostCanonicals(collect([706, 704, 703, 702, 701, 700, 699, 698])
            ->merge($designPostIds)
            ->merge(collect($newsPostIds)->flatten())
            ->merge($aboutFeatureIds)
            ->merge($serviceIds)
            ->merge($introActionIds)
            ->merge($aboutStatIds)
            ->all());

        DB::table('widgets')
            ->whereIn('keyword', [
                'intro',
                'intro-features',
                'intro-services',
                'intro-action',
                'about-hero',
                'about-intro',
                'about-intro-features',
                'about-stats',
                'about-services',
            ])
            ->update([
                'deleted_at' => $now,
                'updated_at' => $now,
            ]);

    }

    private function seedIntroduce(string $aboutText): void
    {
        $items = [
            'block_1_company' => 'AZKTV Việt Nam',
            'block_1_en' => 'Chuyên gia thiết kế phòng karaoke',
            'block_1_description' => $aboutText,
            'block_1_image' => '/userfiles/image/bg-about-hero.png',
            'block_1_button_label' => 'Xem thêm',
            'block_1_button_link' => '#',
            'block_2_content' => $aboutText,
            'block_2_box_1_number' => '10+',
            'block_2_box_1_text' => 'Năm kinh nghiệm',
            'block_2_box_2_number' => '34+',
            'block_2_box_2_text' => 'Tỉnh thành',
            'block_2_box_3_number' => '10+',
            'block_2_box_3_text' => 'Quốc gia',
            'block_2_box_4_number' => '500+',
            'block_2_box_4_text' => 'Dự án hoàn thành',
            'block_3_image_1' => '/uploads/images/thiet-ke/thiet-ke-phong-khach-01.jpg',
            'block_3_image_2' => '/uploads/images/thiet-ke/thiet-ke-phong-giam-doc-01.jpg',
            'block_4_image' => '/userfiles/image/home/about-banner.png',
            'block_4_heading' => 'Thiết kế karaoke trọn gói',
            'block_8_heading' => 'Dịch vụ của chúng tôi',
            'block_8_description' => $aboutText,
            'block_8_image' => '/userfiles/image/home/karaoke-section-bg.png',
            'block_8_block_1_title' => 'Thiết kế 3D',
            'block_8_block_1_description' => 'Lên concept 3D rõ ràng để chốt phương án trước khi thi công.',
            'block_8_block_2_title' => 'Thi công cách âm',
            'block_8_block_2_description' => 'Xử lý cách âm đúng kỹ thuật cho phòng hát vận hành ổn định.',
            'block_8_block_3_title' => 'Lắp đặt âm thanh',
            'block_8_block_3_description' => 'Tư vấn và lắp đặt hệ thống âm thanh phù hợp mô hình sử dụng.',
            'block_8_block_4_title' => 'Ánh sáng & nội thất',
            'block_8_block_4_description' => 'Hoàn thiện ánh sáng, nội thất và điểm nhấn nhận diện phòng hát.',
            'block_9_block_1_title' => 'Kinh nghiệm thực tế',
            'block_9_block_1_description' => 'Đội ngũ đã triển khai nhiều mô hình phòng karaoke thực tế.',
            'block_9_block_2_title' => 'Thi công chuẩn kỹ thuật',
            'block_9_block_2_description' => 'Quy trình thi công bám sát tiêu chuẩn âm học và vận hành.',
            'block_9_block_3_title' => 'Thiết kế sáng tạo',
            'block_9_block_3_description' => 'Concept thiết kế riêng theo mô hình kinh doanh và ngân sách.',
            'block_9_block_4_title' => 'Chi phí tối ưu',
            'block_9_block_4_description' => 'Tối ưu vật liệu, thiết bị và ngân sách đầu tư thực tế.',
        ];

        foreach ($items as $keyword => $content) {
            DB::table('introduces')->updateOrInsert(
                ['keyword' => $keyword, 'language_id' => $this->languageId],
                [
                    'content' => $content,
                    'user_id' => $this->userId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedPosts(array $items): array
    {
        $ids = [];
        foreach ($items as $index => [$name, $description, $image]) {
            $ids[] = $this->upsertPost($name, $description, $image, $index);
        }
        return $ids;
    }

    private function seedProducts(array $items): array
    {
        $ids = [];
        foreach ($items as $index => [$canonical, $name, $description, $image]) {
            $ids[] = $this->upsertProduct($canonical, $name, $description, $image, $index);
        }
        return $ids;
    }

    private function upsertPost(string $name, string $description, string $image, int $order): int
    {
        $now = now();
        $postId = DB::table('post_language')
            ->where('language_id', $this->languageId)
            ->where('name', $name)
            ->value('post_id');

        $payload = [
            'post_catalogue_id' => 0,
            'image' => $image,
            'album' => json_encode([]),
            'pubish' => 2,
            'order' => 1000 - $order,
            'user_id' => $this->userId,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        if ($postId) {
            DB::table('posts')->where('id', $postId)->update($payload);
        } else {
            $payload['created_at'] = $now;
            $postId = DB::table('posts')->insertGetId($payload);
        }

        $languagePayload = [
            'name' => $name,
            'description' => $description,
            'content' => $description,
            'meta_title' => $name,
            'meta_keyword' => $name,
            'meta_description' => $description,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('post_language', 'canonical')) {
            $currentCanonical = DB::table('post_language')
                ->where('post_id', $postId)
                ->where('language_id', $this->languageId)
                ->value('canonical');

            $languagePayload['canonical'] = $currentCanonical ?: $this->canonicalFromName($name, $postId);
        }

        DB::table('post_language')->updateOrInsert(
            ['post_id' => $postId, 'language_id' => $this->languageId],
            $languagePayload
        );

        return (int) $postId;
    }

    private function fillPostCanonicals(array $postIds): void
    {
        if (!Schema::hasColumn('post_language', 'canonical')) {
            return;
        }

        $postIds = collect($postIds)
            ->flatten()
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($postIds->isEmpty()) {
            return;
        }

        $rows = DB::table('post_language')
            ->where('language_id', $this->languageId)
            ->whereIn('post_id', $postIds)
            ->where(function ($query) {
                $query->whereNull('canonical')->orWhere('canonical', '');
            })
            ->get(['post_id', 'name']);

        foreach ($rows as $row) {
            DB::table('post_language')
                ->where('post_id', $row->post_id)
                ->where('language_id', $this->languageId)
                ->update([
                    'canonical' => $this->canonicalFromName((string) $row->name, (int) $row->post_id),
                    'updated_at' => now(),
                ]);
        }
    }

    private function canonicalFromName(string $name, int $id): string
    {
        $canonical = Str::slug($name);

        return $canonical !== '' ? "{$canonical}-{$id}" : "post-{$id}";
    }

    private function upsertProduct(string $canonical, string $name, string $description, string $image, int $order): int
    {
        $now = now();
        $productId = DB::table('product_language')
            ->where('language_id', $this->languageId)
            ->where('canonical', $canonical)
            ->value('product_id');

        $payload = [
            'product_catalogue_id' => 0,
            'image' => $image,
            'album' => json_encode([]),
            'publish' => 2,
            'follow' => 2,
            'order' => 1000 - $order,
            'user_id' => $this->userId,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        if ($productId) {
            DB::table('products')->where('id', $productId)->update($payload);
        } else {
            $payload['created_at'] = $now;
            $productId = DB::table('products')->insertGetId($payload);
        }

        DB::table('product_language')->updateOrInsert(
            ['product_id' => $productId, 'language_id' => $this->languageId],
            [
                'name' => $name,
                'description' => $description,
                'content' => $description,
                'meta_title' => $name,
                'meta_keyword' => $name,
                'meta_description' => $description,
                'canonical' => $canonical,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) $productId;
    }

    private function upsertWidget(string $keyword, string $name, string $model, array $modelIds, array|string $description, string $note): void
    {
        $now = now();
        DB::table('widgets')->updateOrInsert(
            ['keyword' => $keyword],
            [
                'name' => $name,
                'description' => json_encode([$this->languageId => $description], JSON_UNESCAPED_UNICODE),
                'album' => json_encode([]),
                'model_id' => json_encode(array_values($modelIds)),
                'model' => $model,
                'short_code' => '',
                'publish' => 2,
                'note' => $note,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function prioritizePostsInCatalogue(int $catalogueId, array $postIds): void
    {
        foreach (array_values($postIds) as $index => $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'pubish' => 2,
                'order' => 10000 - $index,
                'deleted_at' => null,
            ]);

            DB::table('post_catalogue_post')->updateOrInsert(
                [
                    'post_id' => $postId,
                    'post_catalogue_id' => $catalogueId,
                ]
            );
        }
    }

    private function updatePostPresentation(int $postId, string $shortName, string $icon): void
    {
        DB::table('posts')->where('id', $postId)->update([
            'short_name' => $shortName,
            'icon' => $icon,
            'pubish' => 2,
            'deleted_at' => null,
        ]);
    }
}
