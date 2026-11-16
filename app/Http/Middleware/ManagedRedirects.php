<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ap dung cac chuyen huong khai bao trong admin (bang `redirects`).
 *
 * Dat o middleware TOAN CUC, truoc khi dinh tuyen, nen bat duoc ca URL dang
 * 404 lan URL van con trang that (vd muon don 2 bai trung nhau ve 1 bai).
 * Ban do duoc cache vinh vien va chi xoa khi admin luu/xoa, nen request binh
 * thuong chi ton mot lan doc cache file.
 */
class ManagedRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return $next($request);
        }

        try {
            $map = Redirect::map();
        } catch (\Throwable $e) {
            // Bang chua tao (chua chay deploy/setup-redirects.php) hoac DB loi:
            // khong duoc lam sap site vi tinh nang phu nay.
            return $next($request);
        }

        if (!$map) {
            return $next($request);
        }

        $path = Redirect::normalizePath('/' . ltrim($request->getPathInfo(), '/'));
        $hit  = $map[mb_strtolower($path)] ?? null;

        if (!$hit) {
            return $next($request);
        }

        [$target, $code, $id] = $hit;

        if (!preg_match('#^https?://#i', $target)) {
            $target = url('/' . ltrim($target, '/'));
        }

        // Giu query string (utm_..., trang=...) neu dich chua co query rieng.
        $qs = $request->getQueryString();
        if ($qs && !str_contains($target, '?')) {
            $target .= '?' . $qs;
        }

        try {
            DB::table('redirects')->where('id', $id)->update([
                'hits'        => DB::raw('hits + 1'),
                'last_hit_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ManagedRedirects: khong dem duoc luot', ['id' => $id, 'e' => $e->getMessage()]);
        }

        return redirect()->away($target, in_array($code, [301, 302], true) ? $code : 301);
    }
}
