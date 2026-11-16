<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Doan ma nhung (widget, pixel, chat, form...) do admin dan vao muc
 * "Vi Tri Chen Ma". Hien ra o giao dien qua @snippet('vi-tri').
 * Luu DB nen git pull khong ghi de.
 */
class CodeSnippet extends Model
{
    protected $table = 'code_snippets';

    protected $fillable = ['name', 'position', 'code', 'pages', 'urls', 'publish', 'sort', 'note'];

    public const CACHE_KEY = 'code_snippets.v1';

    /**
     * Cac vi tri da dat san trong giao dien. Them vi tri moi = them 1 dong o
     * day + 1 dong @snippet('ma') o dung cho trong view.
     */
    public const POSITIONS = [
        'head_end'              => 'Cuối thẻ <head> (mã xác minh, pixel, CSS)',
        'body_start'            => 'Ngay sau thẻ <body> (Google Tag Manager noscript)',
        'body_end'              => 'Cuối trang, trước </body> (widget chat, popup - khuyên dùng)',
        'header_after'          => 'Ngay dưới menu / header',
        'footer_before'         => 'Ngay trên footer',
        'post_content_before'   => 'Bài viết: đầu nội dung',
        'post_content_after'    => 'Bài viết: cuối nội dung (trước nút chia sẻ)',
        'product_buy_after'     => 'Sản phẩm: dưới nút Liên hệ / Gọi ngay',
        'product_content_after' => 'Sản phẩm: cuối tab mô tả chi tiết',
        'contact_after'         => 'Trang liên hệ: cuối trang',
    ];

    /** Loai trang - gan tu ten view chinh (xem AppServiceProvider). */
    public const PAGES = [
        'home'              => 'Trang chủ',
        'post'              => 'Bài viết (kể cả lời bài hát)',
        'post_catalogue'    => 'Danh mục bài viết',
        'product'           => 'Chi tiết sản phẩm',
        'product_catalogue' => 'Danh mục sản phẩm',
        'search'            => 'Tìm kiếm',
        'contact'           => 'Liên hệ',
        'other'             => 'Trang khác (giỏ hàng, tài khoản...)',
    ];

    public function pageList(): array
    {
        return array_values(array_filter(explode(',', (string) $this->pages)));
    }

    public function urlList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->urls))));
    }

    /** Cac doan dang bat, nhom theo vi tri. */
    public static function active(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $out = [];
            foreach (static::query()->where('publish', 2)->orderBy('sort')->orderBy('id')->get() as $s) {
                $out[$s->position][] = [
                    'code'  => (string) $s->code,
                    'pages' => $s->pageList(),
                    'urls'  => array_map(fn ($u) => mb_strtolower(Redirect::normalizePath($u)), $s->urlList()),
                ];
            }
            return $out;
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
