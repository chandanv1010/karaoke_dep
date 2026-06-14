<?php

namespace App\Support;

use App\Models\MenuCatalogue;
use App\Models\Post;
use App\Models\PostCatalogue;
use App\Models\Product;
use App\Models\ProductCatalogue;
use App\Models\Slide;
use App\Support\SchemaCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyFrontend
{
    public static function image(?string $image, string $fallback = 'images/no-image.jpg'): string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return asset($fallback);
        }

        if (Str::startsWith($image, ['http://', 'https://', '//'])) {
            return $image;
        }

        return asset(ltrim($image, '/'));
    }

    public static function url(?string $canonical, bool $fullDomain = false, bool $suffix = true): string
    {
        $canonical = trim((string) $canonical);

        if ($canonical === '' || $canonical === '#') {
            return $canonical ?: '#';
        }

        if (Str::startsWith($canonical, ['http://', 'https://', 'mailto:', 'tel:', 'sms:', '#', '?'])) {
            return $canonical;
        }

        $path = ltrim($canonical, '/');
        $suffixValue = (string) config('apps.general.suffix', '.html');

        if ($suffix && $suffixValue !== '' && !Str::endsWith($path, $suffixValue)) {
            $path .= $suffixValue;
        }

        return ($fullDomain ? rtrim(config('app.url'), '/') . '/' : url('/')) . '/' . $path;
    }

    public static function system(array $system, string $key, string $default = ''): string
    {
        $aliases = [
            'homepage_brandname' => 'homepage_brand',
            'seo_meta_images' => 'seo_meta_image',
            'seo_meta_keywords' => 'seo_meta_keyword',
        ];

        return (string) ($system[$key] ?? $system[$aliases[$key] ?? ''] ?? $default);
    }

    public static function navigations(string $position, int $language = 1): array
    {
        static $cache = [];

        $cacheKey = "{$position}.{$language}";
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $keywords = array_values(array_unique([
            $position,
            $position . '-menu',
            $position === 'main' ? 'main-menu' : $position,
        ]));

        $catalogue = MenuCatalogue::with([
            'menus' => function ($query) use ($language) {
                $query->where('publish', 2)
                    ->with(['languages' => fn ($q) => $q->where('language_id', $language)])
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'asc');
            },
        ])
            ->whereIn('keyword', $keywords)
            ->where('publish', 2)
            ->first();

        if (!$catalogue) {
            return $cache[$cacheKey] = [];
        }

        return $cache[$cacheKey] = self::menuTree($catalogue->menus);
    }

    public static function slides(array $keywords = ['index-slide'], int $language = 1): array
    {
        $slides = Slide::query()
            ->whereIn('keyword', $keywords)
            ->where('publish', 2)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $output = [];

        foreach ($slides as $slide) {
            $items = $slide->item;
            if (is_string($items)) {
                $items = json_decode($items, true) ?: [];
            }

            $languageItems = $items[$language] ?? $items[(string) $language] ?? reset($items) ?: [];

            $output[$slide->keyword] = [
                'name' => $slide->name,
                'short_code' => $slide->short_code,
                'setting' => is_string($slide->setting) ? json_decode($slide->setting, true) : $slide->setting,
                'item' => collect($languageItems)->map(function ($item) {
                    return [
                        'image' => self::image($item['image'] ?? null),
                        'name' => $item['name'] ?? '',
                        'description' => $item['description'] ?? '',
                        'canonical' => self::url($item['canonical'] ?? '#', false, false),
                        'url' => self::url($item['canonical'] ?? '#', false, false),
                        'alt' => $item['alt'] ?? $item['name'] ?? '',
                        'window' => $item['window'] ?? '',
                    ];
                })->all(),
            ];
        }

        return $output;
    }

    public static function productAsideCategories(int $language = 1, int $limit = 29): array
    {
        $parentField = self::columnExists('product_catalogues', 'parent_id') ? 'parent_id' : 'parentid';

        $parents = ProductCatalogue::query()
            ->select([
                'product_catalogues.*',
                'product_catalogue_language.name',
                'product_catalogue_language.canonical',
                'product_catalogue_language.description',
            ])
            ->join('product_catalogue_language', 'product_catalogue_language.product_catalogue_id', '=', 'product_catalogues.id')
            ->where('product_catalogue_language.language_id', $language)
            ->where("product_catalogues.{$parentField}", 0)
            ->where('product_catalogues.publish', 2)
            ->where('product_catalogues.order', '>', 0)
            ->whereNull('product_catalogues.deleted_at')
            ->orderBy('product_catalogues.order', 'asc')
            ->orderBy('product_catalogues.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($catalogue) => self::productCatalogueArray($catalogue))
            ->all();

        foreach ($parents as $key => $parent) {
            $parents[$key]['child'] = self::productChildren((int) $parent['id'], $language, $limit);
        }

        return $parents;
    }

    public static function headerCommitments(int $language = 1): array
    {
        $items = self::postsQuery($language)
            ->join('post_catalogue_post', 'post_catalogue_post.post_id', '=', 'posts.id')
            ->where('post_catalogue_post.post_catalogue_id', 1)
            ->orderBy('posts.order', 'asc')
            ->orderBy('posts.id', 'desc')
            ->limit(4)
            ->get()
            ->map(fn ($post) => self::postArray($post))
            ->all();

        if (!count($items)) {
            $items = self::postsQuery($language)
                ->orderBy('posts.order', 'asc')
                ->orderBy('posts.id', 'desc')
                ->limit(4)
                ->get()
                ->map(fn ($post) => self::postArray($post))
                ->all();
        }

        return $items;
    }

    public static function randomProducts(int $language = 1, int $limit = 20): array
    {
        return self::productsQuery($language)
            ->orderBy('products.order', 'asc')
            ->orderBy('products.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($product) => self::productArray($product))
            ->all();
    }

    public static function mostViewedPosts(int $language = 1, int $limit = 5): array
    {
        return self::postsQuery($language)
            ->orderBy('posts.viewed', 'desc')
            ->orderBy('posts.order', 'asc')
            ->orderBy('posts.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($post) => self::postArray($post))
            ->all();
    }

    public static function featuredSidebarPosts(int $language = 1): array
    {
        $titles = [
            'Hướng dẫn mua hàng',
            'Hình thức thanh toán',
            'Chính sách và quy định chung',
        ];

        $posts = self::postsQuery($language)
            ->whereIn('post_language.name', $titles)
            ->get()
            ->mapWithKeys(fn ($post) => [$post->name => self::postArray($post)])
            ->all();

        return collect($titles)
            ->map(fn ($title) => $posts[$title] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    public static function supportGroups(): array
    {
        $catalogues = collect(self::legacyArchiveRows('supports_catalogues'))
            ->filter(fn ($row) => (int) ($row['publish'] ?? 0) === 1 && (int) ($row['trash'] ?? 0) === 0)
            ->keyBy(fn ($row) => (int) ($row['id'] ?? 0));

        $supports = collect(self::legacyArchiveRows('supports'))
            ->filter(fn ($row) => (int) ($row['publish'] ?? 0) === 1 && (int) ($row['trash'] ?? 0) === 0)
            ->groupBy(fn ($row) => (int) ($row['cataloguesid'] ?? 0));

        $preferredOrder = [3, 1];

        return collect($preferredOrder)
            ->filter(fn ($catalogueId) => $catalogues->has($catalogueId))
            ->map(function ($catalogueId) use ($catalogues, $supports) {
                $catalogue = $catalogues[$catalogueId];
                $items = ($supports[$catalogueId] ?? collect())
                    ->map(fn ($support) => [
                        'name' => $support['fullname'] ?? '',
                        'phone' => $support['phone'] ?? '',
                    ])
                    ->filter(fn ($support) => trim($support['name'] . $support['phone']) !== '')
                    ->values()
                    ->all();

                return [
                    'title' => $catalogue['title'] ?? '',
                    'items' => $items,
                ];
            })
            ->filter(fn ($group) => count($group['items']) > 0)
            ->values()
            ->all();
    }

    private static function menuTree($menus, int $parentId = 0): array
    {
        $output = [];

        foreach ($menus as $menu) {
            if ((int) $menu->parent_id !== $parentId) {
                continue;
            }

            $language = $menu->languages->first();
            $title = $language?->pivot?->name ?? '';
            $canonical = $language?->pivot?->canonical ?? '';

            $output[] = [
                'id' => $menu->id,
                'title' => $title,
                'href' => self::url($canonical),
                'items' => self::menuTree($menus, (int) $menu->id),
            ];
        }

        return $output;
    }

    public static function homePayload(int $language = 1): array
    {
        $productParentField = self::columnExists('product_catalogues', 'parent_id') ? 'parent_id' : 'parentid';
        $postParentField = self::columnExists('post_catalogues', 'parent_id') ? 'parent_id' : 'parentid';
        $postPublishField = self::columnExists('post_catalogues', 'publish') ? 'publish' : 'pubish';

        $highlightProducts = self::productsQuery($language)
            ->orderBy('products.order', 'asc')
            ->orderBy('products.id', 'desc')
            ->limit(4)
            ->get()
            ->map(fn ($product) => self::productArray($product))
            ->all();

        $productCatalogues = ProductCatalogue::query()
            ->select([
                'product_catalogues.*',
                'product_catalogue_language.name',
                'product_catalogue_language.canonical',
                'product_catalogue_language.description',
                'product_catalogue_language.content',
            ])
            ->join('product_catalogue_language', 'product_catalogue_language.product_catalogue_id', '=', 'product_catalogues.id')
            ->where('product_catalogue_language.language_id', $language)
            ->where("product_catalogues.{$productParentField}", 0)
            ->where('product_catalogues.publish', 2)
            ->whereNull('product_catalogues.deleted_at')
            ->orderBy('product_catalogues.order', 'asc')
            ->orderBy('product_catalogues.id', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($catalogue) use ($language) {
                $item = self::productCatalogueArray($catalogue);
                $item['child'] = self::productChildren($catalogue->id, $language, 8);
                $item['post'] = self::productsByCatalogue($catalogue->id, $language, 8);
                return $item;
            })
            ->all();

        $postCatalogues = PostCatalogue::query()
            ->select([
                'post_catalogues.*',
                'post_catalogue_language.name',
                'post_catalogue_language.canonical',
                'post_catalogue_language.description',
                'post_catalogue_language.content',
            ])
            ->join('post_catalogue_language', 'post_catalogue_language.post_catalogue_id', '=', 'post_catalogues.id')
            ->where('post_catalogue_language.language_id', $language)
            ->where("post_catalogues.{$postParentField}", 0)
            ->where("post_catalogues.{$postPublishField}", 2)
            ->where('post_catalogue_language.canonical', 'tin-tuc-su-kien')
            ->whereNull('post_catalogues.deleted_at')
            ->orderBy('post_catalogues.order', 'asc')
            ->orderBy('post_catalogues.id', 'desc')
            ->limit(1)
            ->get()
            ->map(function ($catalogue) use ($language) {
                $item = self::postCatalogueArray($catalogue);
                $item['child'] = self::postChildren($catalogue->id, $language, 4);
                $item['post'] = self::postsByCatalogue($catalogue->id, $language, 3);
                return $item;
            })
            ->all();

        return [
            'highlight_product' => $highlightProducts,
            'saleoff_product' => [],
            'product_catalogues_is' => $productCatalogues,
            'product_catalogues_hl' => array_slice($productCatalogues, 0, 4),
            'news' => $postCatalogues,
            'highlight_post' => self::postsQuery($language)
                ->orderBy('posts.order', 'asc')
                ->orderBy('posts.id', 'desc')
                ->limit(6)
                ->get()
                ->map(fn ($post) => self::postArray($post))
                ->all(),
        ];
    }

    public static function productCataloguePayload($productCatalogue, $products, $breadcrumb, int $language = 1): array
    {
        $detail = self::productCatalogueArray($productCatalogue);
        $children = self::productChildren((int) $productCatalogue->id, $language, 10);

        if (!count($children)) {
            $children = [[
                ...$detail,
                'post' => self::paginatorItems($products, fn ($product) => self::productArray($product)),
            ]];
        } else {
            foreach ($children as $key => $child) {
                $children[$key]['post'] = self::productsByCatalogue((int) $child['id'], $language, 8);
            }
        }

        return [
            'Breadcrumb' => self::breadcrumbArray($breadcrumb, 'product'),
            'DetailCatalogues' => $detail,
            'child' => $children,
            'productsList' => self::paginatorItems($products, fn ($product) => self::productArray($product)),
            'PaginationList' => $products instanceof LengthAwarePaginator ? self::paginationHtml($products) : '',
            'modules' => 'products_catalogues',
        ];
    }

    public static function productPayload($product, $productCatalogue, $breadcrumb, $related = null, $seen = null): array
    {
        return [
            'Breadcrumb' => self::breadcrumbArray($breadcrumb, 'product'),
            'DetailProducts' => self::productArray($product),
            'DetailCatalogues' => self::productCatalogueArray($productCatalogue),
            'products_same' => self::collectionItems($related, fn ($item) => self::productArray($item)),
            'Seen' => self::collectionItems($seen, fn ($item) => self::productArray($item)),
            'TagsList' => [],
        ];
    }

    public static function postCataloguePayload($postCatalogue, $posts, $breadcrumb, int $language = 1): array
    {
        $children = self::postChildren((int) $postCatalogue->id, $language, 10);

        foreach ($children as $key => $child) {
            $children[$key]['post'] = self::postsByCatalogue((int) $child['id'], $language, 8);
        }

        return [
            'Breadcrumb' => self::breadcrumbArray($breadcrumb, 'post'),
            'DetailCatalogues' => self::postCatalogueArray($postCatalogue),
            'highlight_post' => self::paginatorItems($posts, fn ($post) => self::postArray($post)),
            'child' => $children,
            'ArticlesList' => self::paginatorItems($posts, fn ($post) => self::postArray($post)),
            'most_viewed' => self::mostViewedPosts($language),
            'PaginationList' => $posts instanceof LengthAwarePaginator ? self::paginationHtml($posts) : '',
        ];
    }

    public static function paginationHtml(LengthAwarePaginator $paginator): string
    {
        return $paginator->links('frontend.component.pagination-legacy')->toHtml();
    }

    public static function postPayload($post, $postCatalogue, $breadcrumb, $related = null): array
    {
        return [
            'Breadcrumb' => self::breadcrumbArray($breadcrumb, 'post'),
            'DetailArticles' => self::postArray($post),
            'DetailCatalogues' => self::postCatalogueArray($postCatalogue),
            'articles_same' => self::collectionItems($related, fn ($item) => self::postArray($item)),
            'TagsList' => [],
        ];
    }

    public static function productArray($product): array
    {
        if (!$product) {
            return [];
        }

        $album = $product->album ?? null;

        return [
            'id' => $product->id,
            'title' => self::translatedValue($product, 'name'),
            'slug' => self::translatedValue($product, 'canonical'),
            'canonical' => self::translatedValue($product, 'canonical'),
            'images' => self::image($product->image ?? null),
            'image' => self::image($product->image ?? null),
            'description' => self::translatedValue($product, 'description'),
            'content' => self::translatedValue($product, 'content'),
            'created' => optional($product->created_at)->format('d/m/Y') ?: '',
            'price' => (float) ($product->price ?? 0),
            'saleoff' => (float) ($product->promotion_price ?? $product->combo_price ?? 0),
            'code' => $product->code ?? '',
            'albums' => is_string($album) ? $album : json_encode($album ?: []),
            'videos' => $product->iframe ?? '',
            'viewed' => $product->viewed ?? 0,
            'attributes' => [],
        ];
    }

    public static function postArray($post): array
    {
        if (!$post) {
            return [];
        }

        $canonical = self::translatedValue($post, 'canonical');
        if (empty($canonical) && !empty($post->id)) {
            static $routerCache = [];
            $routerCache[$post->id] ??= DB::table('routers')
                ->where('module_id', $post->id)
                ->where('controllers', 'like', '%PostController%')
                ->value('canonical');
            $canonical = $routerCache[$post->id] ?? '';
        }

        return [
            'id' => $post->id,
            'title' => self::translatedValue($post, 'name'),
            'slug' => $canonical,
            'canonical' => $canonical,
            'images' => self::image($post->image ?? null),
            'image' => self::image($post->image ?? null),
            'description' => self::translatedValue($post, 'description'),
            'content' => self::translatedValue($post, 'content'),
            'created' => optional($post->created_at)->format('d/m/Y') ?: '',
            'viewed' => $post->viewed ?? 0,
        ];
    }

    public static function productCatalogueArray($catalogue): array
    {
        return self::catalogueArray($catalogue, 'product');
    }

    public static function postCatalogueArray($catalogue): array
    {
        return self::catalogueArray($catalogue, 'post');
    }

    private static function catalogueArray($catalogue, string $type): array
    {
        if (!$catalogue) {
            return [];
        }

        return [
            'id' => $catalogue->id,
            'title' => self::translatedValue($catalogue, 'name'),
            'slug' => self::translatedValue($catalogue, 'canonical'),
            'canonical' => self::translatedValue($catalogue, 'canonical'),
            'images' => self::image($catalogue->image ?? null),
            'image' => self::image($catalogue->image ?? null),
            'description' => self::translatedValue($catalogue, 'description'),
            'content' => self::translatedValue($catalogue, 'content'),
            'banner' => '',
            'banner_2' => '',
            'banner_3' => '',
            'type' => $type,
        ];
    }

    private static function pivotValue($model, string $field): ?string
    {
        $language = $model?->languages?->first();

        return $language?->pivot?->{$field};
    }

    private static function translatedValue($model, string $field): ?string
    {
        if (!$model) {
            return null;
        }

        if (method_exists($model, 'getAttributes') && array_key_exists($field, $model->getAttributes())) {
            return $model->getAttribute($field);
        }

        return self::pivotValue($model, $field);
    }

    private static function productsQuery(int $language)
    {
        return Product::query()
            ->select([
                'products.*',
                'product_language.name',
                'product_language.canonical',
                'product_language.description',
                'product_language.content',
                'product_language.meta_title',
                'product_language.meta_keyword',
                'product_language.meta_description',
            ])
            ->join('product_language', 'product_language.product_id', '=', 'products.id')
            ->where('product_language.language_id', $language)
            ->where('products.publish', 2)
            ->whereNull('products.deleted_at');
    }

    public static function postsQuery(int $language)
    {
        $publishField = self::columnExists('posts', 'publish') ? 'publish' : 'pubish';
        $canonicalColumn = self::columnExists('post_language', 'canonical')
            ? 'post_language.canonical'
            : DB::raw("(select canonical from routers where routers.module_id = posts.id and routers.controllers like '%PostController%' limit 1) as canonical");

        return Post::query()
            ->select([
                'posts.*',
                'post_language.name',
                $canonicalColumn,
                'post_language.description',
                'post_language.content',
                'post_language.meta_title',
                'post_language.meta_keyword',
                'post_language.meta_description',
            ])
            ->join('post_language', 'post_language.post_id', '=', 'posts.id')
            ->where('post_language.language_id', $language)
            ->where("posts.{$publishField}", 2)
            ->whereNull('posts.deleted_at');
    }

    private static function productsByCatalogue(int $catalogueId, int $language, int $limit): array
    {
        return self::productsQuery($language)
            ->join('product_catalogue_product', 'product_catalogue_product.product_id', '=', 'products.id')
            ->where('product_catalogue_product.product_catalogue_id', $catalogueId)
            ->orderBy('products.order', 'asc')
            ->orderBy('products.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($product) => self::productArray($product))
            ->all();
    }

    private static function postsByCatalogue(int $catalogueId, int $language, int $limit): array
    {
        return self::postsQuery($language)
            ->join('post_catalogue_post', 'post_catalogue_post.post_id', '=', 'posts.id')
            ->where('post_catalogue_post.post_catalogue_id', $catalogueId)
            ->orderBy('posts.order', 'asc')
            ->orderBy('posts.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($post) => self::postArray($post))
            ->all();
    }

    private static function productChildren(int $parentId, int $language, int $limit): array
    {
        return ProductCatalogue::query()
            ->select([
                'product_catalogues.*',
                'product_catalogue_language.name',
                'product_catalogue_language.canonical',
                'product_catalogue_language.description',
                'product_catalogue_language.content',
            ])
            ->join('product_catalogue_language', 'product_catalogue_language.product_catalogue_id', '=', 'product_catalogues.id')
            ->where('product_catalogue_language.language_id', $language)
            ->where('product_catalogues.parent_id', $parentId)
            ->where('product_catalogues.publish', 2)
            ->whereNull('product_catalogues.deleted_at')
            ->orderBy('product_catalogues.order', 'asc')
            ->orderBy('product_catalogues.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($catalogue) => self::productCatalogueArray($catalogue))
            ->all();
    }

    private static function postChildren(int $parentId, int $language, int $limit): array
    {
        $parentField = self::columnExists('post_catalogues', 'parent_id') ? 'parent_id' : 'parentid';
        $publishField = self::columnExists('post_catalogues', 'publish') ? 'publish' : 'pubish';

        return PostCatalogue::query()
            ->select([
                'post_catalogues.*',
                'post_catalogue_language.name',
                'post_catalogue_language.canonical',
                'post_catalogue_language.description',
                'post_catalogue_language.content',
            ])
            ->join('post_catalogue_language', 'post_catalogue_language.post_catalogue_id', '=', 'post_catalogues.id')
            ->where('post_catalogue_language.language_id', $language)
            ->where("post_catalogues.{$parentField}", $parentId)
            ->where("post_catalogues.{$publishField}", 2)
            ->whereNull('post_catalogues.deleted_at')
            ->orderBy('post_catalogues.order', 'asc')
            ->orderBy('post_catalogues.id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($catalogue) => self::postCatalogueArray($catalogue))
            ->all();
    }

    private static function breadcrumbArray($breadcrumb, string $type): array
    {
        return self::collectionItems($breadcrumb, function ($item) use ($type) {
            return $type === 'product'
                ? self::productCatalogueArray($item)
                : self::postCatalogueArray($item);
        });
    }

    private static function paginatorItems($items, callable $mapper): array
    {
        if ($items instanceof Paginator) {
            return collect($items->items())->map($mapper)->all();
        }

        return self::collectionItems($items, $mapper);
    }

    private static function collectionItems($items, callable $mapper): array
    {
        if ($items instanceof Collection || $items instanceof EloquentCollection) {
            return $items->map($mapper)->all();
        }

        if (is_array($items)) {
            return collect($items)->map($mapper)->all();
        }

        return [];
    }

    private static function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = "{$table}.{$column}";

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = SchemaCache::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    private static function legacyArchiveRows(string $sourceTable): array
    {
        if (!SchemaCache::hasTable('legacy_import_records')) {
            return [];
        }

        return DB::table('legacy_import_records')
            ->where('source_table', $sourceTable)
            ->orderBy('source_pk')
            ->pluck('payload')
            ->map(fn ($payload) => json_decode((string) $payload, true))
            ->filter(fn ($row) => is_array($row))
            ->values()
            ->all();
    }
}
