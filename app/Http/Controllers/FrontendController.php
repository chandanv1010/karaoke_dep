<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Language;
use App\Models\System;
use Illuminate\Support\Facades\Cache;

class FrontendController extends Controller
{
    protected $language;
    protected $systemRepository;
    protected $system;

    public function __construct(
        // SystemRepository $systemRepository
    ){

        $this->setLanguage();
        $this->setSystem();

    }

    public function setLanguage(){
        $locale = app()->getLocale(); // vn en cn
        // $language = Language::where('canonical', $locale)->first();
        $this->language = 1;
    }

    /** Key cache cau hinh he thong theo tung ngon ngu. */
    public static function systemCacheKey(int $languageId): string
    {
        return 'frontend.system.' . $languageId;
    }

    public function setSystem(){
        // Bang systems duoc doc o MOI request de dung cho layout (logo, hotline,
        // SEO, script...) nhung noi dung chi doi khi admin luu cau hinh. Truoc
        // day day la query cham nhat tren moi trang. Cache lai va xoa cache
        // trong SystemService::save() khi admin luu.
        // TTL 1 gio la luoi an toan: neu vi ly do nao do cache khong duoc xoa
        // thi cung tu het han, khong ket cau hinh cu vinh vien.
        $load = fn () => convert_array(
            System::where('language_id', $this->language)->get(),
            'keyword',
            'content'
        );

        // FAIL-SAFE: neu cache khong ghi/doc duoc (vi du storage/framework/cache
        // khong co quyen ghi - da gap that khi thu nghiem, lam 500 toan site)
        // thi doc thang tu DB. Mot toi uu toc do khong duoc phep lam sap trang.
        try {
            $this->system = Cache::remember(
                self::systemCacheKey((int) $this->language),
                3600,
                $load
            );
        } catch (\Throwable $e) {
            report($e);
            $this->system = $load();
        }
    }
   

}
