<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Sinh sitemap.xml tu CSDL.
 *
 * TRUOC DAY: public/sitemap.xml la file TINH do xml-sitemaps.com xuat ra, lan
 * cap nhat cuoi la 30/6/2026. Moi bai viet va san pham them vao sau ngay do
 * deu khong co trong sitemap, con nhung trang da xoa thi van nam lai. Google
 * doc mot ban do loi thoi 4 thang.
 *
 * NAY: doc thang tu bang `routers` - chinh la bang ma RouterController dung de
 * phan giai URL, nen sitemap luon khop voi nhung gi site that su phuc vu.
 *
 * Luu y ve ten cot: `posts` va `post_catalogues` dung cot `pubish` (thieu chu
 * 'l', loi chinh ta co san trong lugc do CSDL), con `products` va
 * `product_catalogues` dung `publish`. Gia tri da xuat ban la 2, khong phai 1.
 * Viet sai ten cot o day thi sitemap se rong ma khong bao loi gi.
 */
class SitemapController extends Controller
{
    /** Cache 1 gio: bot co the goi lien tuc, khong can dung 4 truy van moi lan. */
    private const CACHE_SECONDS = 3600;

    /**
     * Moi module: bang noi dung, bang ban dich, va ten cot xuat ban.
     * do_uu_tien / tan_suat theo khuyen nghi cua sitemaps.org.
     */
    private const MODULES = [
        'ProductCatalogueController' => ['product_catalogues', 'publish', '0.9', 'weekly'],
        'PostCatalogueController'    => ['post_catalogues',    'pubish',  '0.8', 'weekly'],
        'ProductController'          => ['products',           'publish', '0.7', 'monthly'],
        'PostController'             => ['posts',              'pubish',  '0.6', 'monthly'],
    ];

    public function index()
    {
        $xml = Cache::remember('sitemap.xml', self::CACHE_SECONDS, fn () => $this->build());

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function build(): string
    {
        $suffix = (string) config('apps.general.suffix');
        $base   = rtrim((string) config('app.url'), '/');

        $urls = [[
            'loc'        => $base . '/',
            'lastmod'    => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ]];

        foreach (self::MODULES as $controller => [$table, $publishColumn, $priority, $changefreq]) {
            $rows = DB::table('routers')
                ->join($table, $table . '.id', '=', 'routers.module_id')
                ->where('routers.controllers', 'like', '%' . $controller)
                ->where($table . '.' . $publishColumn, 2)
                // deleted_at o cac bang nay la NULL khi chua xoa, khong phai 0.
                // Chi so sanh voi 0 thi truy van loc sach moi dong va sitemap
                // chi con moi trang chu - khong bao loi gi ca.
                ->where(function ($q) use ($table) {
                    $q->whereNull($table . '.deleted_at')
                      ->orWhere($table . '.deleted_at', 0);
                })
                ->where('routers.canonical', '<>', '')
                ->select('routers.canonical', $table . '.updated_at')
                ->orderBy($table . '.updated_at', 'desc')
                ->get();

            foreach ($rows as $row) {
                $urls[] = [
                    'loc'        => $base . '/' . ltrim($row->canonical, '/') . $suffix,
                    'lastmod'    => $this->atom($row->updated_at),
                    'changefreq' => $changefreq,
                    'priority'   => $priority,
                ];
            }
        }

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $out .= '  <url>' . "\n"
                 .  '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n"
                 .  ($u['lastmod'] ? '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n" : '')
                 .  '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n"
                 .  '    <priority>' . $u['priority'] . '</priority>' . "\n"
                 .  '  </url>' . "\n";
        }

        return $out . '</urlset>' . "\n";
    }

    /** updated_at co the rong hoac la ngay 0000-00-00 -> bo qua lastmod. */
    private function atom($value): ?string
    {
        if (empty($value) || str_starts_with((string) $value, '0000')) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toAtomString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
