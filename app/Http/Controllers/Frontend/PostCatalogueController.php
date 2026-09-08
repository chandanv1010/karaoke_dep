<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use App\Repositories\Post\PostCatalogueRepository;
use App\Services\V1\Post\PostCatalogueService;
use App\Services\V1\Post\PostService;
use App\Services\V1\Core\WidgetService;
use App\Services\V1\Core\SlideService;

use App\Models\System;
use App\Enums\SlideEnum;
use Jenssegers\Agent\Facades\Agent;
use App\Models\Introduce;
use App\Models\Post;
use App\Support\LegacyFrontend;

class PostCatalogueController extends FrontendController
{
    use \App\Traits\RendersSchema;

    protected $language;
    protected $system;
    protected $postCatalogueRepository;
    protected $postCatalogueService;
    protected $postService;
    protected $widgetService;
    protected $slideService;

    public function __construct(
        PostCatalogueRepository $postCatalogueRepository,
        PostCatalogueService $postCatalogueService,
        PostService $postService,
        WidgetService $widgetService,
        SlideService $slideService,
    ) {
        $this->postCatalogueRepository = $postCatalogueRepository;
        $this->postCatalogueService = $postCatalogueService;
        $this->postService = $postService;
        $this->widgetService = $widgetService;
        $this->slideService = $slideService;
        parent::__construct();
    }


    public function index($id, $request, $page = 1)
    {
        $postCatalogue = $this->postCatalogueRepository->getPostCatalogueById($id, $this->language);
        $postCatalogue->children = $this->postCatalogueRepository->findByCondition(
            [
                ['publish', '=', 2],
                ['parent_id', '=', $postCatalogue->id]
            ],
            true,
            [],
            ['order', 'desc']
        );
        
        $breadcrumb = $this->postCatalogueRepository->breadcrumb($postCatalogue, $this->language);
        $posts = $this->postService->paginate(
            $request,
            $this->language,
            $postCatalogue,
            $page,
            ['path' => $postCatalogue->canonical],
            // Sap theo bai moi nhat. Truoc day sap theo 'posts.recommend' nhung
            // ca 657 bai deu co recommend = 1 nen dieu kien nay la hang so,
            // khong co tac dung gi; thu tu thuc te roi ve tiebreaker id DESC.
            // Dung created_at moi dung nghia "moi nhat": bang co 330 ngay khac
            // nhau tu 2017 den 2026, va id khong khop thu tu ngay vi du lieu
            // duoc migrate tu he thong cu.
            ['posts.created_at', 'desc']
        );

        // dd($posts->toArray());

        $featuredPost = $this->postCatalogueRepository->getFeaturedPost($postCatalogue);

        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'featured-products'],
            ['keyword' => 'product-category', 'children' => true],
            ['keyword' => 'product-category-highlight', 'object' => true],
            ['keyword' => 'about-us-2'],
            ['keyword' => 'karaoke-construction', 'object' => true],
        ], $this->language);

        $slides = $this->slideService->getSlide(
            [SlideEnum::MAIN],
            $this->language
        );
        $lastestNews = LegacyFrontend::postsQuery($this->language)->orderBy('posts.order', 'desc')->orderBy('posts.id', 'desc')->limit(8)->get();
        // dd($lastestNews);

        if(in_array($postCatalogue->canonical, ['ve-chung-toi', 'gioi-thieu'])){
            $template = 'frontend.post.catalogue.about';
        }else{
            $template = 'frontend.post.catalogue.index';
        }

        $config = $this->config();
        $system = $this->system;
        $seo = seo($postCatalogue, $page);
        $introduce = convert_array(Introduce::where('language_id', $this->language)->get(), 'keyword', 'content');
        $schema = $this->schema($postCatalogue, $posts, $breadcrumb);
        $legacy = LegacyFrontend::postCataloguePayload($postCatalogue, $posts, $breadcrumb, $this->language);
        return view($template, compact(
            'config',
            'seo',
            'system',
            'breadcrumb',
            'postCatalogue',
            'posts',
            'widgets',
            'schema',
            'slides',
            'introduce',
            'lastestNews'
        ) + $legacy);
    }

    private function schema($postCatalogue, $posts, $breadcrumb)
    {
        $cat_name = $postCatalogue->languages->first()->pivot->name;
        $cat_canonical = write_url($postCatalogue->languages->first()->pivot->canonical);
        $cat_description = $this->schemaText($postCatalogue->languages->first()->pivot->description, 5000);

        // "blogPost" phai la mang cac BlogPosting, truoc day bi boc trong { } nen vo JSON.
        $blogPosts = [];
        foreach ($posts as $post) {
            $blogPosts[] = [
                '@type' => 'BlogPosting',
                // Dung cot da JOIN san (PostService::paginateColumns() select
                // tb2.name) thay vi quan he languages: goi ->languages->first()
                // trong vong lap nay sinh N+1, moi bai viet mot query.
                'headline' => (string) ($post->name ?? ''),
                'url' => write_url($post->canonical ?? ''),
                'datePublished' => (string) convertDateTime($post->created_at, 'd-m-Y'),
                'author' => [
                    '@type' => 'Organization',
                    'name' => 'An Hưng',
                ],
            ];
        }

        $breadcrumbItems = [[
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Trang chủ',
            'item' => config('app.url'),
        ]];
        $positionBreadcrumb = 2;
        foreach ($breadcrumb as $item) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $positionBreadcrumb++,
                'name' => (string) $item->languages->first()->pivot->name,
                'item' => write_url($item->languages->first()->pivot->canonical),
            ];
        }

        return $this->renderSchema([
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ],
            [
                '@type' => 'Blog',
                'name' => trim($cat_name),
                'description' => trim($cat_description),
                'url' => trim($cat_canonical),
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'An Hưng',
                ],
                'blogPost' => $blogPosts,
            ],
        ]);
    }

   


    private function config()
    {
        return [
            'language' => $this->language,
            'css' => [
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css',
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css',
                'frontend/resources/css/custom.css'
            ],
            'js' => [
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/owl.carousel.min.js',
                'frontend/resources/library/js/carousel.js',
                'https://getuikit.com/v2/src/js/components/sticky.js'
            ]
        ];
    }

}
