<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dien width/height that vao moi <img> con thieu trong trang HTML tra ve.
 *
 * Bao cao kiem toan ngay 04/11/2026: ~49% anh khong khai bao kich thuoc nen
 * trinh duyet khong giu cho truoc khi anh tai xong -> nhay layout (CLS). Anh
 * thieu nam rai rac o hang chuc template (the san pham 600x0, slider 800x0,
 * nen 1600x0, anh chinh san pham...), nen sua mot cho o dau ra thay vi sua tung
 * view - template moi them sau nay cung duoc phu luon.
 *
 * Kich thuoc tinh dung nhu ImageResizerController (/image-cache/{w}x{h}/...):
 * giu ty le, KHONG phong to qua anh goc. width/height cua the yeu hon moi quy
 * tac CSS, nen chi cung cap ty le khung hinh - da do tren site that (desktop va
 * 375px, 4 kieu trang, ~120 anh): khong anh nao doi kich thuoc hien thi.
 *
 * BO QUA anh co width/height trong style="" (vd logo Bo Cong Thuong chi dat
 * height:40px; them thuoc tinh width se keo gian khung tu 103px len 218px),
 * anh ngoai site, va anh khong doc duoc file goc.
 */
class AddImageDimensions
{
    private const DIRS = '(?:uploads|userfiles|images|templates)';

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$request->isMethod('GET')
            || $response->getStatusCode() !== 200
            || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || !method_exists($response, 'getContent')
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse
            || $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return $response;
        }

        $html = $response->getContent();
        if (!is_string($html) || !str_contains($html, '<img')) {
            return $response;
        }

        $new = preg_replace_callback('#<img\b[^>]*>#i', fn ($m) => $this->fix($m[0]), $html);
        if (is_string($new) && $new !== $html) {
            $response->setContent($new);
        }

        return $response;
    }

    private function fix(string $tag): string
    {
        if (preg_match('#\s(width|height)\s*=#i', $tag)) {
            return $tag;
        }
        if (preg_match('#\sstyle\s*=\s*("[^"]*"|\'[^\']*\')#i', $tag, $st)
            && preg_match('#(^|[;\s"\'])(width|height)\s*:#i', $st[1])) {
            return $tag;
        }
        if (!preg_match('#\ssrc\s*=\s*("([^"]*)"|\'([^\']*)\')#i', $tag, $s)) {
            return $tag;
        }

        $dims = $this->dimensions(html_entity_decode($s[2] !== '' ? $s[2] : ($s[3] ?? ''), ENT_QUOTES));
        if ($dims === null) {
            return $tag;
        }

        [$w, $h] = $dims;
        return preg_replace('#^<img\b#i', '<img width="' . $w . '" height="' . $h . '"', $tag, 1);
    }

    private function dimensions(string $src): ?array
    {
        $path = parse_url($src, PHP_URL_PATH);
        $host = parse_url($src, PHP_URL_HOST);
        if (!$path || ($host && strcasecmp(preg_replace('#^www\.#i', '', $host), request()->getHost()) !== 0)) {
            return null;
        }
        $path = rawurldecode($path);
        if (str_contains($path, '..')) {
            return null;
        }

        $tw = $th = 0;
        if (preg_match('#^/image-cache/(\d+)x(\d+)/(.+)$#', $path, $m)) {
            [$tw, $th, $path] = [(int) $m[1], (int) $m[2], '/' . $m[3]];
        }
        if (!preg_match('#^/' . self::DIRS . '/.+\.(jpe?g|png|gif|webp)$#i', $path)) {
            return null;
        }

        static $sizes = [];
        $full = public_path(ltrim($path, '/'));
        if (!array_key_exists($full, $sizes)) {
            $info = is_file($full) ? @getimagesize($full) : false;
            $sizes[$full] = ($info && $info[0] > 0 && $info[1] > 0) ? [(int) $info[0], (int) $info[1]] : null;
        }
        if ($sizes[$full] === null) {
            return null;
        }
        [$ow, $oh] = $sizes[$full];

        // Giong ImageResizerController::targetSize() + chan phong to.
        if ($tw && $th) {
            [$w, $h] = [$tw, $th];
        } elseif ($tw) {
            [$w, $h] = [$tw, max(1, (int) round($oh * $tw / $ow))];
        } elseif ($th) {
            [$w, $h] = [max(1, (int) round($ow * $th / $oh)), $th];
        } else {
            return [$ow, $oh];
        }
        if ($w >= $ow && $h >= $oh) {
            return [$ow, $oh];
        }
        return [$w, $h];
    }
}
