<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;


use App\Repositories\Product\ProductCatalogueRepository;
use App\Services\V1\Product\ProductCatalogueService;
use App\Services\V1\Product\ProductService;
use App\Services\V1\Core\WidgetService;
use App\Repositories\Product\ProductRepository;
use App\Services\V1\Product\CompareService;


use Gloudemans\Shoppingcart\Facades\Cart;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\Post;
use App\Support\LegacyFrontend;

class ProductCatalogueController extends FrontendController
{
    use \App\Traits\RendersSchema;

    protected $language;
    protected $system;
    protected $productCatalogueRepository;
    protected $productCatalogueService;
    protected $productService;
    protected $widgetService;
    protected $productRepository;
    protected $lecturerRepository;
    protected $compareService;

    public function __construct(
        ProductCatalogueRepository $productCatalogueRepository,
        ProductCatalogueService $productCatalogueService,
        ProductService $productService,
        ProductRepository $productRepository,
        WidgetService $widgetService,
        CompareService $compareService,
    ) {
        $this->productCatalogueRepository = $productCatalogueRepository;
        $this->productCatalogueService = $productCatalogueService;
        $this->productService = $productService;
        $this->widgetService = $widgetService;
        $this->productRepository = $productRepository;
        $this->compareService = $compareService;
        parent::__construct();
    }


    public function index($id, $request, $page = 1)
    {
        $productCatalogue = $this->productCatalogueRepository->getProductCatalogueById($id, $this->language);
        if (is_null($productCatalogue)) {
            abort(404);
        }
        $parent = null;
        $descendantTrees = null;
        $descendantTrees = $this->productCatalogueService->getChildren();
        $filters = $this->filter($productCatalogue);
        $breadcrumb = $this->productCatalogueRepository->breadcrumb($productCatalogue, $this->language);
        $products = $this->productService->paginate(
            $request,
            $this->language,
            $productCatalogue,
            $page,
            ['path' => $productCatalogue->canonical, 'perPage' => 18],
        );
        $products = $this->combineProductValues($products);
        $productCatalogues = recursive($this->productCatalogueRepository->all(['languages']));
        // dd($productCatalogues);
        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'featured-products'],
            ['keyword' => 'product-category', 'children' => true],
            ['keyword' => 'product-category-highlight', 'object' => true],
            ['keyword' => 'about-us-2'],
            ['keyword' => 'karaoke-construction'],
        ], $this->language);
        $config = $this->config();
        $system = $this->system;
        $seo = seo($productCatalogue, $page);
        $schema = $this->schema($productCatalogue, $products, $breadcrumb);
        $legacy = LegacyFrontend::productCataloguePayload($productCatalogue, $products, $breadcrumb, $this->language);
        $template = 'frontend.product.catalogue.index';
        return view($template, compact(
            'descendantTrees',
            'config',
            'seo',
            'system',
            'breadcrumb',
            'productCatalogue',
            'products',
            'filters',
            'widgets',
            'schema',
            'productCatalogues'
        ) + $legacy);
    }

    private function combineProductValues($products)
    {
        $productId = $products->pluck('id')->toArray();
        if (count($productId) && !is_null($productId)) {
            $products = $this->productService->combineProductAndPromotion($productId, $products);
            $products = $this->productService->combineProductRelation($products);
        }

        return $products;
    }

    private function filter($productCatalogue)
    {
        $filters = null;
        $children = $this->productCatalogueRepository->getChildren($productCatalogue);
        $groupedAttributes = [];
        foreach ($children as $child) {
            if (isset($child->attribute) && !is_null($child->attribute) && count($child->attribute)) {
                foreach ($child->attribute as $key => $value) {
                    if (!isset($groupedAttributes[$key])) {
                        $groupedAttributes[$key] = [];
                    }
                    $groupedAttributes[$key][] = $value;
                }
            }
        }
        foreach ($groupedAttributes as $key => $value) {
            $groupedAttributes[$key] = array_merge(...$value);
        }

        if (isset($groupedAttributes) && !is_null($groupedAttributes) && count($groupedAttributes)) {
            $filters = $this->productCatalogueService->getFilterList($groupedAttributes, $this->language);
        }
        return $filters;
    }


    public function search(Request $request)
    {

        $products = $this->productRepository->search($request->input('keyword'), $this->language);

        $productId = $products->pluck('id')->toArray();

        if (count($productId) && !is_null($productId)) {
            $products = $this->productService->combineProductAndPromotion($productId, $products);
        }

        $config = $this->config();

        $system = $this->system;

        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'news-outstanding', 'object' => true],
        ], $this->language);

        $seo = [
            'meta_title' => 'Tìm kiếm cho từ khóa: ' . $request->input('keyword'),
            'meta_keyword' => '',
            'meta_description' => '',
            'meta_image' => '',
            'canonical' => write_url('tim-kiem')
        ];

        $template = 'frontend.product.catalogue.search';


        $productItems = method_exists($products, 'items') ? $products->items() : $products;
        $legacy = [
            'productsList' => collect($productItems)->map(fn ($product) => LegacyFrontend::productArray($product))->all(),
            'PaginationList' => $products instanceof LengthAwarePaginator ? LegacyFrontend::paginationHtml($products) : '',
        ];

        return view($template, compact(
            'config',
            'seo',
            'system',
            'products',
            'widgets'
        ) + $legacy);
    }

    public function wishlist(Request $request)
    {
        $wishlistItems = Cart::instance('wishlist')->content();
        $ids = $wishlistItems->pluck('id')->map(function ($id) {
            return (int)$id;
        })->filter()->values()->toArray();

        $products = collect();
        if (!empty($ids)) {
            $products = $this->productRepository->findByIds($ids, $this->language);
            $products = $this->productService->combineProductAndPromotion($ids, $products);
            $products = $products->sortBy(function ($product) use ($ids) {
                return array_search($product->id, $ids);
            })->values();
        }

        $perPage = 8;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $wishlistProducts = new LengthAwarePaginator(
            $products->forPage($page, $perPage),
            $products->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $config = $this->config();
        $system = $this->system;
        $seo = [
            'meta_title' => 'Danh sách yêu thích',
            'meta_keyword' => '',
            'meta_description' => '',
            'meta_image' => '',
            'canonical' => write_url('yeu-thich')
        ];
        $wishlistCount = Cart::instance('wishlist')->count();

        return view('frontend.product.catalogue.wishlist', [
            'config' => $config,
            'seo' => $seo,
            'system' => $system,
            'products' => $wishlistProducts,
            'wishlistCount' => $wishlistCount,
        ]);
    }

    public function compare(Request $request)
    {
        $comparePayload = $this->compareService->getPayload($this->language);

        $config = $this->config();
        $system = $this->system;
        $seo = [
            'meta_title' => 'So sánh sản phẩm',
            'meta_keyword' => '',
            'meta_description' => '',
            'meta_image' => '',
            'canonical' => write_url('so-sanh'),
        ];

        return view('frontend.product.catalogue.compare', array_merge($comparePayload, [
            'config' => $config,
            'seo' => $seo,
            'system' => $system,
            'maxCompareItems' => CompareService::MAX_ITEMS,
        ]));
    }

    private function schema($productCatalogue, $products, $breadcrumb)
    {
        $cat_name = $productCatalogue->languages->first()->pivot->name;
        $cat_canonical = write_url($productCatalogue->languages->first()->pivot->canonical);
        $cat_description = $this->schemaText($productCatalogue->languages->first()->pivot->description, 5000);
        $totalProducts = $products->total();

        $itemListElements = [];
        $position = 1;
        foreach ($products as $product) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => [
                    '@type' => 'Product',
                    // Dung truc tiep cot da JOIN san thay vi quan he languages.
                    // ProductService::paginateSelect() da select tb2.name va
                    // tb2.canonical, nen $product->name / ->canonical co san.
                    // Goi ->languages->first() trong vong lap nay sinh N+1:
                    // 18 query product_language cho mot trang danh muc.
                    'name' => (string) ($product->name ?? ''),
                    'url' => write_url($product->canonical ?? ''),
                    'image' => (string) $product->image,
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
                '@type' => 'CollectionPage',
                'name' => trim($cat_name),
                'description' => trim($cat_description),
                'url' => trim($cat_canonical),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'name' => trim($cat_name),
                    'numberOfItems' => (int) $totalProducts,
                    'itemListElement' => $itemListElements,
                ],
            ],
        ]);
    }

    private function config()
    {
        return [
            'language' => $this->language,
            'externalJs' => [
                '//code.jquery.com/ui/1.11.4/jquery-ui.js'
            ],
            'css' => [
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css',
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css',
                'frontend/resources/css/custom.css',
            ],
            'js' => [
                'frontend/core/library/filter.js',
                'frontend/resources/plugins/OwlCarousel2-2.3.4/dist/owl.carousel.min.js',
                'frontend/resources/library/js/carousel.js',
            ],

        ];
    }
}
