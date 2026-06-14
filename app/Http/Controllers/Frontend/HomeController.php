<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;

use App\Repositories\Core\SystemRepository;
use App\Services\V1\Core\SlideService;
use App\Enums\SlideEnum;
use App\Services\V1\Core\WidgetService;
use Illuminate\Http\Request;
use App\Support\LegacyFrontend;
use App\Models\Introduce;
use Jenssegers\Agent\Facades\Agent;

class HomeController extends FrontendController
{
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
        $template = 'frontend.homepage.home.index';
        return view($template, compact(
            'config',
            'slides',
            'seo',
            'system',
            'schema',
            'widgets',
            'introduce',
        ) + $legacy);
    }

    /**
         * @param array $seo
         * @return string
         */
        public function schema(array $seo = []): string
        {
            $schema = "<script type='application/ld+json'>
                {
                    \"@context\": \"https://schema.org\",
                    \"@type\": \"WebSite\",
                    \"name\": \"" . ($seo['meta_title'] ?? '') . "\",
                    \"url\": \"" . ($seo['canonical'] ?? '') . "\",
                    \"description\": \"" . ($seo['meta_description'] ?? '') . "\",
                    \"publisher\": {
                        \"@type\": \"Organization\",
                        \"name\": \"" . ($seo['meta_title'] ?? '') . "\"
                    },
                    \"potentialAction\": {
                        \"@type\": \"SearchAction\",
                        \"target\": {
                            \"@type\": \"EntryPoint\",
                            \"urlTemplate\": \"" . ($seo['canonical'] ?? '') . "search?q={search_term_string}\"
                        },
                        \"query-input\": \"required name=search_term_string\"
                    }
                }
            </script>";

            return $schema;
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
