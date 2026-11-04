<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;

use App\Repositories\Core\SystemRepository;
use App\Services\V1\Core\SlideService;
use App\Enums\SlideEnum;
use App\Services\V1\Core\WidgetService;
use Illuminate\Http\Request;
use App\Traits\RendersSchema;
use App\Support\LegacyFrontend;
use App\Models\Introduce;
use Jenssegers\Agent\Facades\Agent;

class HomeController extends FrontendController
{
    use RendersSchema;

    protected $systemRepository;
    protected $slideService;
    protected $widgetService;
    protected $scholarService;

    public function __construct(
        SlideService $slideService,
        SystemRepository $systemRepository,
        WidgetService $widgetService,
    ) {
        $this->slideService = $slideService;
        $this->systemRepository = $systemRepository;
        $this->widgetService = $widgetService;
        
        parent::__construct(
            $systemRepository,
        );
    }


    public function index()
    {
        $config = $this->config();


        $slides = $this->slideService->getSlide(
            [SlideEnum::MAIN, 'index-slide', 'home-banner', SlideEnum::TECHSTAFF, SlideEnum::PARTNER],
            $this->language
        );

        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'home-designs-1', 'object' => true, 'limit' => 30],
            ['keyword' => 'home-designs-2', 'object' => true, 'limit' => 30],
            ['keyword' => 'home-news-1', 'object' => true, 'limit' => 30],
            ['keyword' => 'home-news-2', 'object' => true, 'limit' => 30],
            ['keyword' => 'home-news-3', 'object' => true, 'limit' => 30],
            ['keyword' => 'karaoke-construction', 'object' => true],
            ['keyword' => 'featured-products'],
            ['keyword' => 'bring'],
            ['keyword' => 'p-hl'],
            ['keyword' => 'category', 'children' => true],
            ['keyword' => 'feedback', 'object' => true],
            ['keyword' => 'news', 'object' => true],
            ['keyword' => 'value', 'object' => true],
            ['keyword' => 'ship'],
        ], $this->language);


        $system = $this->system;
        $introduce = convert_array(Introduce::where('language_id', $this->language)->get(), 'keyword', 'content');
        $seo = [
            'meta_title' => $this->system['seo_meta_title'] ?? $this->system['homepage_company'] ?? config('app.name'),
            'meta_keyword' => $this->system['seo_meta_keyword'] ?? '',
            'meta_description' => $this->system['seo_meta_description'] ?? '',
            'meta_image' => $this->system['seo_meta_images'] ?? $this->system['homepage_logo'] ?? '',
            'canonical' => config('app.url'),
        ];
        $schema = $this->schema($seo);
        $legacy = LegacyFrontend::homePayload($this->language);

        /* Anh LCP cua trang chu - de layout phat the <link rel="preload">.
         *
         * Phai tinh o day chu khong tinh trong layout: layout chi nhan duoc
         * bien qua @extends, ma @yield('content') chay SAU khi <head> da in
         * xong, nen moi cach lay tu phia noi dung (ke ca @push/@stack) deu
         * toi muon. Truyen thang tu controller la cach duy nhat chac chan.
         *
         * Vi sao can preload: anh hero nam sau rat nhieu HTML va chi hien khi
         * CSS slideshow da ve, nen trinh duyet tim thay no muon - PageSpeed
         * do duoc 1.300ms "resource load delay" trước cả lúc bắt đầu tải.
         */
        $lcpImage = ($slides['index-slide']['item'][0]['image'] ?? null);

        $template = 'frontend.homepage.home.index';
        return view($template, compact(
            'config',
            'slides',
            'seo',
            'system',
            'schema',
            'widgets',
            'introduce',
            'lcpImage',
        ) + $legacy);
    }

    /**
         * @param array $seo
         * @return string
         */
        /**
         * JSON-LD trang chu: WebSite (+ o tim kiem) va GeneralContractor.
         *
         * Truoc day noi chuoi JSON bang tay (khong escape) va o tim kiem tro vao
         * /search?q= - duong khong ton tai; trang tim kiem that la
         * /tim-kiem?keyword=. Bao cao kiem toan 04/11/2026 neu thieu
         * LocalBusiness: GeneralContractor la loai LocalBusiness dung nhat cho
         * don vi thiet ke - thi cong. Moi gia tri lay tu bang systems (trang
         * Cau hinh chung), khong ghi cung; truong nao trong thi bo qua.
         */
        public function schema(array $seo = []): string
        {
            $sys  = $this->system ?? [];
            $home = rtrim((string) config('app.url'), '/') . '/';
            $name = $this->schemaText($sys['homepage_company'] ?? '') ?: $this->schemaText($seo['meta_title'] ?? '');

            $website = [
                '@type' => 'WebSite',
                '@id' => $home . '#website',
                'name' => $this->schemaText($sys['homepage_brandname'] ?? '') ?: $name,
                'url' => $home,
                'description' => $this->schemaText($seo['meta_description'] ?? ''),
                'inLanguage' => 'vi-VN',
                'publisher' => ['@id' => $home . '#business'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $home . 'tim-kiem?keyword={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ];

            $phone = preg_replace('/\D/', '', (string) ($sys['contact_hotline'] ?? $sys['contact_phone'] ?? ''));
            $business = [
                '@type' => 'GeneralContractor',
                '@id' => $home . '#business',
                'name' => $name,
                'alternateName' => 'AZKTV',
                'url' => $home,
                'logo' => $this->schemaUrl($sys['homepage_logo'] ?? ''),
                'image' => $this->schemaUrl($sys['seo_meta_images'] ?? $sys['homepage_logo'] ?? ''),
                'slogan' => $this->schemaText($sys['homepage_slogan'] ?? ''),
                'telephone' => $phone !== '' ? '+84' . ltrim($phone, '0') : '',
                'email' => (string) ($sys['contact_email'] ?? ''),
                'address' => $this->schemaAddress($sys['contact_address'] ?? ''),
                'areaServed' => ['@type' => 'Country', 'name' => 'Việt Nam'],
                'sameAs' => array_values(array_filter(array_map(
                    fn ($k) => preg_match('#^https://#', (string) ($sys[$k] ?? '')) ? $sys[$k] : null,
                    ['seo_facebook', 'seo_youtube', 'seo_twitter', 'seo_pinterest', 'seo_linkedin']
                ))),
            ];

            // Toa do lay tu iframe Google Maps trong Cau hinh (…!2d{lng}!3d{lat}…).
            if (preg_match('/!2d(-?[\d.]+)!3d(-?[\d.]+)/', (string) ($sys['contact_map'] ?? ''), $g)) {
                $business['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $g[2], 'longitude' => (float) $g[1]];
            }

            // Chi nhanh: dia chi 2, 3 trong Cau hinh.
            $branches = [];
            // Mot o co the ghi hai co so: "Tru so VP: ... - Xuong san xuat va chi nhanh VP: ...".
            foreach (['contact_address_2', 'contact_address_3'] as $k) {
                foreach (preg_split('/\s+-\s+(?=[^,]{0,40}:)/u', $this->schemaText($sys[$k] ?? '')) as $part) {
                    $part = preg_replace('/^[^,:]{1,40}:\s*/u', '', $part);
                    if ($addr = $this->schemaAddress($part)) {
                        $branches[] = ['@type' => 'GeneralContractor', 'name' => $name, 'address' => $addr, 'telephone' => $business['telephone']];
                    }
                }
            }
            if ($branches) {
                $business['department'] = $branches;
            }

            $business = array_filter($business, fn ($v) => $v !== '' && $v !== null && $v !== []);

            return $this->renderSchema([$website, $business]);
        }

        /** Dia chi dang chu tu Cau hinh -> PostalAddress (Viet Nam). */
        private function schemaAddress($text): ?array
        {
            $text = trim(preg_replace('/\s+/u', ' ', $this->schemaText($text)), " .");
            if ($text === '') {
                return null;
            }
            $region = null;
            foreach (['Hà Nội', 'Đà Nẵng', 'Hồ Chí Minh', 'HCM'] as $city) {
                if (mb_stripos($text, $city) !== false) {
                    $region = in_array($city, ['Hồ Chí Minh', 'HCM'], true) ? 'TP Hồ Chí Minh' : $city;
                    break;
                }
            }
            return array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $text,
                'addressRegion' => $region,
                'addressCountry' => 'VN',
            ]);
        }

    private function config()
    {
        return [
            'language' => $this->language,
            'css' => [
                '__frontend/resources/style.css'
            ],
            'js' => []
        ];
    }



}
