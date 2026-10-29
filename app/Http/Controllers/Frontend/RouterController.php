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
            echo app($this->router->controllers)->{$method}($this->router->module_id, $request);
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
            echo app($this->router->controllers)->{$method}($this->router->module_id, $request, $page);
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
     *     /...dao-nam-toan-quoc-a650.html  -> post_language id = 650
     *     /...rach-gia-kien-giang-a447.html -> post_language id = 447
     *
     * Ham nay cat hau to roi tra ve duong dan moi NEU no that su ton tai.
     * Khong ton tai thi van de 404 that - khong chuyen huong bua ve trang chu,
     * vi Google coi do la "soft 404" va con phat nang hon.
     *
     * Chay o nhanh 404 nen khong them chi phi cho request binh thuong.
     */
    private function legacyRedirect(string $canonical): ?string
    {
        if (!preg_match('#^(.+)-a\d+$#', $canonical, $m)) {
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