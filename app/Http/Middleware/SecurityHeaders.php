<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gan cac header bao mat, va dep not duong /index.php trung lap.
 *
 * Bao cao kiem toan ngay 29/10/2026 neu hai viec, kiem chung lai ngay
 * 30/10/2026 thi ca hai deu con dung:
 *
 *   - 8.873 tai nguyen (96,67%) thieu X-Frame-Options, X-Content-Type-Options,
 *     Referrer-Policy va Content-Security-Policy. Chi co sang
 *     Strict-Transport-Security.
 *   - /index.php tra ve HTTP 200 chu khong chuyen huong, nen moi trang ton tai
 *     hai ban dia chi. Bao cao dem duoc 915 URL trung lap vi ly do nay.
 *
 * VI SAO LAM O DAY MA KHONG SUA NGINX
 * Cau hinh nginx cua aaPanel nam ngoai repo, khong ai theo doi duoc lich su
 * thay doi, va lan nang cap bang panel co the ghi de. De trong middleware thi
 * no di theo ma nguon.
 *
 * VI SAO CSP CHI CO HAI CHI THI
 * Mot CSP day du (script-src, style-src...) se chan dung jQuery, UIkit, the
 * Google, tracker Zalo va hang loat doan script/style inline trong noi dung
 * CMS - tuc la lam hong trang ngay lap tuc. Hai chi thi duoi day khong han
 * che nguon script nao:
 *   - frame-ancestors 'self'  : chong clickjacking, manh hon X-Frame-Options
 *   - upgrade-insecure-requests: tu nang request http:// cung domain len https,
 *     lam luoi an toan cho 4.289 lien ket http:// con sot trong noi dung CMS.
 * Muon siet CSP that thi phai kiem ke het script roi chay Report-Only truoc,
 * day la viec rieng chu khong gan vao day duoc.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // /index.php va /index.php/duong-dan deu la ban trung cua duong sach.
        //
        // Phai doc getRequestUri() chu KHONG phai getPathInfo(): khi request di
        // qua front controller public/index.php thi PATH_INFO da bi cat con "/",
        // nen kiem tra bang getPathInfo() se khong bao gio khop.
        $path = (string) parse_url($request->getRequestUri(), PHP_URL_PATH);
        if (preg_match('#^/index\.php(/.*)?$#i', $path, $m)) {
            $target = rtrim(config('app.url'), '/') . ($m[1] ?? '');
            $query = $request->getQueryString();

            return redirect($query ? $target . '?' . $query : ($target ?: '/'), 301);
        }

        // Moc do: middleware nay dung dau nhom 'web' nen thoi diem no chay
        // xap xi "da khoi dong xong framework, chua dung toi controller".
        $tBoot = microtime(true);

        $response = $next($request);

        $tDone = microtime(true);

        // Khong dung headers->set() de ghi de neu nginx da gan san header nao do.
        $headers = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "frame-ancestors 'self'; upgrade-insecure-requests",
        ];

        foreach ($headers as $name => $value) {
            if (!$response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // Thoi gian PHP that su xu ly request, tinh tu luc public/index.php chay.
        // Can de tach bach: trong TTFB ~855ms do tu ngoai vao thi bao nhieu la
        // PHP, bao nhieu la mang + TLS + hang doi php-fpm. Khong co so nay thi
        // moi phuong an tang toc deu la doan mo.
        if (defined('LARAVEL_START')) {
            $response->headers->set('Server-Timing', sprintf(
                'boot;dur=%.1f, render;dur=%.1f, app;dur=%.1f',
                ($tBoot - LARAVEL_START) * 1000,
                ($tDone - $tBoot) * 1000,
                ($tDone - LARAVEL_START) * 1000
            ));
        }

        // TAM THOI (30/10/2026): soi trang thai OPcache ngay trong tien trinh
        // php-fpm. CLI co ban OPcache rieng nen do o CLI khong noi len gi.
        // Go header nay sau khi chan doan xong.
        if (function_exists('opcache_get_status')) {
            $o = @opcache_get_status(false);
            $response->headers->set('X-Opcache', $o === false ? 'TAT' : sprintf(
                'hit=%.1f%% scripts=%d mem=%dMB/%dMB restarts=%d oom=%d',
                $o['opcache_statistics']['opcache_hit_rate'],
                $o['opcache_statistics']['num_cached_scripts'],
                round($o['memory_usage']['used_memory'] / 1048576),
                round(($o['memory_usage']['used_memory'] + $o['memory_usage']['free_memory']) / 1048576),
                $o['opcache_statistics']['oom_restarts'] + $o['opcache_statistics']['hash_restarts'] + $o['opcache_statistics']['manual_restarts'],
                $o['opcache_statistics']['oom_restarts']
            ));
        }

        return $response;
    }
}
