<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Chuyen huong 301/302 do quan tri vien tu khai bao trong admin
 * (QL Chuyen Huong). Thay cho .htaccess: site chay nginx nen .htaccess khong
 * co tac dung, va du lieu nam trong DB nen git pull khong bao gio ghi de.
 */
class Redirect extends Model
{
    protected $table = 'redirects';

    protected $fillable = ['source_path', 'target_url', 'status_code', 'publish', 'note'];

    protected $casts = ['last_hit_at' => 'datetime'];

    public const CACHE_KEY = 'redirects.map.v1';

    /**
     * Dua moi kieu nguoi dung go vao ve cung mot dang de so khop:
     *   https://karaokedep.com.vn/abc.html?x=1  ->  /abc.html
     *   abc.html                                ->  /abc.html
     *   /Ph%C3%B2ng.html                         ->  /Phòng.html
     * Bo query string va dau / cuoi (tru trang chu).
     */
    public static function normalizePath(?string $input): string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $input) || str_starts_with($input, '//')) {
            $input = (string) (parse_url($input, PHP_URL_PATH) ?? '/');
        }

        $input = preg_replace('/[?#].*$/s', '', $input);
        $input = rawurldecode($input);
        $input = '/' . ltrim($input, '/');

        return $input === '/' ? '/' : rtrim($input, '/');
    }

    /** Ban do source_path => [target, code, id], chi cac dong dang bat. */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()
                ->where('publish', 2)
                ->get(['id', 'source_path', 'target_url', 'status_code'])
                ->mapWithKeys(fn ($r) => [
                    mb_strtolower($r->source_path) => [$r->target_url, (int) $r->status_code, $r->id],
                ])
                ->all();
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
