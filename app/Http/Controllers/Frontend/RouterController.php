<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;
use App\Repositories\Core\RouterRepository;

class RouterController extends FrontendController
{
    protected $language;
    protected $routerRepository;
    protected $router;

    public function __construct(
        RouterRepository $routerRepository,
    ) {
        $this->routerRepository = $routerRepository;
        parent::__construct();
    }


    public function index(string $canonical = '', Request $request)
    {
        $this->getRouter($canonical);
        if (!is_null($this->router) && !empty($this->router)) {
            $method = 'index';

            // PHAI return chu khong duoc echo. Bon controller module deu
            // return view(...), nen echo se stringify view va DAY OUTPUT RA
            // NGAY - header khong con gan duoc nua. Hau qua do duoc ngay
            // 30/10/2026: toan bo trang route dong (bai viet, san pham, danh
            // muc - tuc phan lon site) KHONG he nhan duoc X-Frame-Options,
            // X-Content-Type-Options, Referrer-Policy hay CSP, du middleware
            // da dang ky dung. Chi trang chu va vai route tinh la co.
            return app($this->router->controllers)->{$method}($this->router->module_id, $request);
        } else {
            if ($target = $this->legacyRedirect($canonical)) {
                return redirect($target, 301);
            }
            abort(404);
        }
    }

    public function page(string $canonical = '', $page = 1, Request $request)
    {
        $this->getRouter($canonical);
        $request->merge(['page' => $page]);
        $page = (!isset($page)) ? 1 : $page;
        if (!is_null($this->router) && !empty($this->router)) {
            $method = 'index';

            // Xem ghi chu o index(): phai return, khong duoc echo.
            return app($this->router->controllers)->{$method}($this->router->module_id, $request, $page);
        } else {
            if ($target = $this->legacyRedirect($canonical)) {
                return redirect($target, 301);
            }
            abort(404);
        }
    }

    /**
     * Site tung dung duong dan dang {slug}-a{id}.html, sau do bo hau to -a{id}
     * ma khong dat chuyen huong. Hau qua do duoc trong Search Console:
     * 60 trang bao "Khong tim thay (404)", trong do co nhung bai Google da
     * biet tu 2022 - moi backlink tro vao do deu roi xuong 404 va mat sach
     * gia tri. Vi du doi chieu duoc:
     *
     *     /...dao-nam-toan-quoc-a650.html   -> post_language id = 650
     *     /...rach-gia-kien-giang-a447.html -> post_language id = 447
     *     /loa-karaoke-latop-lv15-p225.html -> product_language id = 225
     *
     * Ban dau ham nay chi bat -a{id}. Do lai ngay 30/10/2026 thi 12 trong so
     * 60 URL 404 la san pham dang -p{id} - van rot 404. Nay bat ca hai.
     *
     * Ham nay cat hau to roi tra ve duong dan moi NEU no that su ton tai.
     * Khong ton tai thi van de 404 that - khong chuyen huong bua ve trang chu,
     * vi Google coi do la "soft 404" va con phat nang hon.
     *
     * Chay o nhanh 404 nen khong them chi phi cho request binh thuong.
     */
    private function legacyRedirect(string $canonical): ?string
    {
        if (!preg_match('#^(.+)-[ap]\d+$#', $canonical, $m)) {
            return null;
        }

        $slug = $m[1];
        $router = $this->routerRepository->findByCondition([
            ['canonical', '=', $slug],
            ['language_id', '=', $this->language],
        ]);

        if (is_null($router) || empty($router)) {
            return null;
        }

        return url($slug . config('apps.general.suffix'));
    }

    /**
     * Chay khi khong route nao khop. Sua may kieu URL hong ma Search Console
     * dang bao 404, nhung CHI chuyen huong khi dich that su ton tai - khong
     * bao gio nem bua ve trang chu (Google coi do la "soft 404", phat nang hon
     * la de 404 that).
     *
     * Ba kieu dem duoc trong bao cao ngay 30/10/2026:
     *
     *   1. Rac dinh sau duoi .html. Trong noi dung CMS co the <a> thieu dau
     *      nhay dong nen Googlebot ghep luon chu phia sau vao URL:
     *          /am-thanh-caf.htmlchinh hang CAF
     *          /thi-cong-karaoke.html-am.html
     *   2. Thieu chu l o duoi file:   /thi-cong-karaoke.htm
     *   3. Khong co duoi .html:        /thiet-ke-phong-karaoke
     *
     * Sua xong con thu tiep legacyRedirect() de bat ca /slug-p225.html + rac.
     */
    public function fallback(Request $request)
    {
        $suffix = config('apps.general.suffix');
        $path   = trim(urldecode($request->path()), '/');

        if ($path === '') {
            abort(404);
        }

        $tries = [];

        // 1. Cat phan rac dinh sau duoi .html
        if (@preg_match('#^(.+?' . preg_quote($suffix, '#') . ').+$#us', $path, $m)) {
            $tries[] = $m[1];
        }

        // 2. .htm -> .html
        if (str_ends_with($path, '.htm')) {
            $tries[] = $path . 'l';
        }

        // 3. Thieu han duoi
        if (!str_contains(basename($path), '.')) {
            $tries[] = $path . $suffix;
        }

        foreach ($tries as $try) {
            if (!str_ends_with($try, $suffix)) {
                continue;
            }

            $canonical = substr($try, 0, -strlen($suffix));

            // Phai dung hinh dang canonical thi moi tra DB (giong rang buoc
            // where() cua route dong).
            if (!preg_match('#^[a-zA-Z0-9-]+$#', $canonical)) {
                continue;
            }

            $this->getRouter($canonical);
            if (!is_null($this->router) && !empty($this->router)) {
                return redirect(url($try), 301);
            }

            if ($target = $this->legacyRedirect($canonical)) {
                return redirect($target, 301);
            }
        }

        abort(404);
    }

    public function getRouter($canonical)
    {
        $this->router = $this->routerRepository->findByCondition(
            [
                ['canonical', '=', $canonical],
                ['language_id', '=', $this->language]
            ]
        );
    }

}