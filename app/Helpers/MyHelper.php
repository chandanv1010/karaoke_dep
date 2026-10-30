<?php

use App\Enums\PromotionEnum;

if(!function_exists('convertRevenueChartData')){
    function convertRevenueChartData($chartData, $data = 'monthly_revenue', $label = 'month' , $text = 'Tháng'){
        $newArray = [];
        if(!is_null($chartData) && count($chartData)){
            foreach($chartData as $key => $val){
                $newArray['data'][] = $val->{$data};
                $newArray['label'][] = $text.' '.$val->{$label};
            }
        }
        return $newArray;
    }
}


if(!function_exists('growHtml')){
    function growHtml($grow){
        if($grow > 0){
            return '<div class="stat-percent font-bold text-success">'.$grow.'% <i class="fa fa-level-up"></i></div>';
        }else{
            return '<div class="stat-percent font-bold text-danger">'.$grow.'% <i class="fa fa-level-down"></i></div>';
        }
    }
}

if(!function_exists('growth')){
    function growth($currentValue, $previousValue){
        $divison = ($previousValue == 0) ? 1 : $previousValue;
        $grow =  ($currentValue - $previousValue) / $divison * 100;
        return number_format($grow, 1);
    }
}

if(!function_exists('pre')){
    function pre($data = ''){
        echo '<pre>';
        print_r($data);
        echo '<pre>';
        die();
    }
}

if(!function_exists('image')){
    function image($image){
        
        if(is_null($image)) return 'backend/img/not-found.jpg';

        $image = str_replace('/public/', '/', $image);

        return $image;
    }
}

if(!function_exists('getGiaoHangNhanhToken')){
    function getGiaoHangNhanhToken(){
       return '21e62550-a35f-11ef-a89d-dab02cbaab48';
    }
}


if(!function_exists('convert_price')){
    function convert_price(mixed $price = '', $flag = false){
        if($price === null) return 0;
        return ($flag === false) ? str_replace('.','', $price) : number_format($price, 0, ',', '.');
    }
}

if(!function_exists('getPercent')){
    function getPercent($product = null, $discountValue = 0){
        return ($product->price > 0) ? round($discountValue/$product->price*100) : 0;
    
    }
}

if(!function_exists('getPromotionPrice')){
    function getPromotionPrice($priceMain = 0, $discountValue = 0){
       

        return $priceMain - $discountValue;
    
    }
}


// if(!function_exists('getPrice')){
//     function getPrice($product = null){
//         $result = [
//             'price' => $product->price, 
//             'priceSale' => 0,
//             'percent' => 0, 
//             'html' => ''
//         ];

//         if($product->price == 0){

//             $result['html'] .= '<div class="price mt10">';
//                 $result['html'] .= '<div class="price-sale">Liên Hệ</div>';
//             $result['html'] .= '</div>';
//             return $result;
//         }

//         if(isset($product->promotions) && isset($product->promotions->discountType)){
//             $result['percent'] = getPercent($product, $product->promotions->discount);
//             if($product->promotions->discountValue > 0){
//                 $result['priceSale'] = getPromotionPrice($product->price, $product->promotions->discount);
//             }
//         }
//         $result['html'] .= '<div class="price uk-flex uk-flex-middle mt10">';
//             $result['html'] .= '<div class="price-sale">'.(($result['priceSale'] > 0) ? convert_price($result['priceSale'], true) : convert_price($result['price'], true) ).'<span class="currency">₫</span></div>';
//             if($result['priceSale'] > 0){
//                 $result['html'] .= '<div class="price-old uk-flex uk-flex-middle">'.convert_price($result['price'], true).'đ <div class="percent"><div class="percent-value">-'.$result['percent'].'%</div></div></div>';
                
//             }
//         $result['html'] .= '</div>';
//         return $result;
//     }
// }

if(!function_exists('getPrice')){
    function getPrice($product = null){
        $result = [
            'price' => $product->price, 
            'priceSale' => 0,
            'percent' => 0, 
            'html' => ''
        ];

        // Nếu giá = 0 → Liên hệ
        if($product->price == 0){
            $result['html'] .= '<div class="price mt10">';
            $result['html'] .= '   <div class="price-sale">Liên Hệ</div>';
            $result['html'] .= '</div>';
            return $result;
        }

        // Xử lý khuyến mãi
        if(isset($product->promotions) && isset($product->promotions->discountType)){
            $result['percent'] = getPercent($product, $product->promotions->discount);
            if($product->promotions->discountValue > 0){
                $result['priceSale'] = getPromotionPrice($product->price, $product->promotions->discount);
            }
        }

        // Render HTML
        $result['html'] .= '<div class="price uk-flex uk-flex-middle mt10">';
        
        // Giá sale hoặc giá gốc
        $result['html'] .= '   <div class="price-sale">'
                                . (($result['priceSale'] > 0) ? convert_price($result['priceSale'], true) : convert_price($result['price'], true))
                                . '<span class="currency">₫</span></div>';

        // Nếu có giá sale → hiển thị giá gạch + phần trăm GIÁM GIÁ (được tách riêng)
        if($result['priceSale'] > 0){

            // giá gạch
            $result['html'] .= '   <div class="price-old">'
                                    . convert_price($result['price'], true) . '₫'
                                . '</div>';

            // phần trăm GIẢM (tách hẳn ra → KHÔNG bị gạch ngang)
            $result['html'] .= '   <div class="percent"><div class="percent-value">-'
                                    . $result['percent'] . '%'
                                . '</div></div>';
        }

        $result['html'] .= '</div>';

        return $result;
    }
}

if(!function_exists('getVariantPrice')){
    function getVariantPrice($variant, $variantPromotion){
        $result = [
            'price' => $variant->price, 
            'priceSale' => 0,
            'percent' => 0, 
            'html' => ''
        ];

        if($variant->price == 0){

            $result['html'] .= '<div class="price mt10">';
                $result['html'] .= '<div class="price-sale">Liên Hệ</div>';
            $result['html'] .= '</div>';
            return $result;
        }

        if(!is_null($variantPromotion)){
            $result['percent'] = getPercent($variant, $variantPromotion->discount);
            $result['priceSale'] = getPromotionPrice($variant->price, $variantPromotion->discount);
        }


        $result['html'] .= '<div class="price-sale">'.(($result['priceSale'] > 0) ? convert_price($result['priceSale'], true) : convert_price($result['price'], true) ).'đ</div>';
        if($result['priceSale'] !== $result['price']){
            $result['html'] .= '<div class="price-old">'.convert_price($result['price'], true).'đ <div class="percent"><div class="percent-value">-'.$result['percent'].'%</div></div></div>';
        }
        return $result;
    }
}


if(!function_exists('getReview')){
    function getReview($product = null){

        /** @var $product Product */
        $totalReviews = $product->reviews()->count();
        $totalRate = number_format($product->reviews()->avg('score'), 1);
        $starPercent = ($totalReviews == 0) ? '0' : $totalRate/5*100;

        return [
            'star' => $starPercent,
            'count' => $totalReviews,
            'totalRate' => $totalRate
        ];
        
    }
}


if(!function_exists('convert_array')){
    function convert_array($system = null, $keyword = '', $value = ''){
        $temp = [];
        if(is_array($system)){
            foreach($system as $key => $val){
                $temp[$val[$keyword]] = $val[$value];
            }
        }
        if(is_object($system)){
            foreach($system as $key => $val){
                $temp[$val->{$keyword}] = $val->{$value};
            }
        }

        return $temp;
    }
}

if(!function_exists('convertDateTime')){
    function convertDateTime(string $date = '', string $format = 'd/m/Y H:i', string $inputDateFormat = 'Y-m-d H:i:s'){
       $carbonDate = \Carbon\Carbon::createFromFormat($inputDateFormat, $date);

       return $carbonDate->format($format);
    }
}

if(!function_exists('renderDiscountInformation')){
    function renderDiscountInformation($promotion = null){
        if($promotion->method === 'product_and_quantity'){
            $discountValue = $promotion->discountInformation['info']['discountValue'];
            $discountType = ($promotion->discountInformation['info']['discountType'] == 'percent') ? '%' : 'đ';
            return '<span class="label label-success">'.$discountValue.$discountType.' </span>';
        }
        return  '<div><a href="'.route('promotion.edit', $promotion->id).'">Xem chi tiết</a></div>';
    }
}

if(!function_exists('renderDiscountVoucher')){
    function renderDiscountVoucher($voucher = null){
        $discount_value = $voucher->discount_value;
        $discount_type = ($voucher->discount_type == 'PERCENTAGE') ? '%' : 'đ';
        return '<span class="label label-success">'.$discount_value.$discount_type.' </span>';
    }
}

if(!function_exists('renderSystemInput')){
    function renderSystemInput(string $name = '', $systems = null){
        return '<input 
            type="text"
            name="config['.$name.']"
            value="'.old($name, ($systems[$name]) ?? '').'"
            class="form-control"
            placeholder=""
            autocomplete="off"
        >';
    }
}


if(!function_exists('renderSystemImages')){
    function renderSystemImages(string $name = '', $systems = null){
        return '<input 
            type="text"
            name="config['.$name.']"
            value="'.old($name, ($systems[$name]) ?? '').'"
            class="form-control upload-image"
            placeholder=""
            autocomplete="off"
        >';
    }
}


if(!function_exists('renderSystemTextarea')){
    function renderSystemTextarea(string $name = '', $systems = null){
        return '<textarea name="config['.$name.']" class="form-control system-textarea">'.old($name, ($systems[$name]) ?? '').'</textarea>';
    }
}

if(!function_exists('renderSystemEditor')){
    function renderSystemEditor(string $name = '', $systems = null){
        return '<textarea name="config['.$name.']" id="'.$name.'" class="form-control system-textarea ck-editor">'.old($name, ($systems[$name]) ?? '').'</textarea>';
    }
}

if(!function_exists('renderSystemLink')){
    function renderSystemLink(array $item = [], $systems = null){
        return (isset($item['link'])) ? '<a class="system-link" target="'.$item['link']['target'].'" href="'.$item['link']['href'].'">'.$item['link']['text'].'</a>' : '';
    }
}

if(!function_exists('renderSystemTitle')){
    function renderSystemTitle(array $item = [], $systems = null){
        return (isset($item['title'])) ? '<span class="system-link text-danger">'.$item['title'].'</span>' : '';
    }
}

if(!function_exists('renderSystemSelect')){
    function renderSystemSelect(array $item, string $name = '', $systems = null){
       $html = '<select name="config['.$name.']" class="form-control">';
            foreach($item['option'] as $key => $val){
                $html .= '<option '.((isset($systems[$name]) && $key == $systems[$name]) ? 'selected' : '').' value="'.$key.'">'.$val.'</option>';
            }
       $html .= '</select>';

       return $html;
    }
}

if(!function_exists('write_url')){
    function write_url($canonical = null, bool $fullDomain = true, $suffix = true){
        return \App\Support\LegacyFrontend::url($canonical, $fullDomain, $suffix);
    }
}

if(!function_exists('rewrite_url')){
    function rewrite_url($canonical = null, $slug = null, $id = null, $module = null, $fullDomain = false, $suffix = true){
        return \App\Support\LegacyFrontend::url($canonical ?: $slug, $fullDomain, $suffix);
    }
}

if(!function_exists('pagination_legacy_url')){
    function pagination_legacy_url(?string $url, int $page = 1): string
    {
        if (!$url) {
            return '#';
        }

        $paginationUrl = str_replace('?page=', '/trang-', $url) . config('apps.general.suffix');

        if ($page === 1) {
            $paginationUrl = str_replace('/trang-' . $page, '', $paginationUrl);
        }

        return $paginationUrl;
    }
}

if(!function_exists('thumb_cache_path')){
    /**
     * Duong dan tuong doi cua ban anh da resize, dung lam ca URL lan duong dan
     * file tren dia:
     *
     *     /image-cache/600x0/uploads/images/abc.jpg
     *                  ^^^^^ rong x cao, 0 = khong chi dinh
     *
     * Co y KHONG bam ma (hash): duong dan tu mo ta duoc chinh no, nen khi
     * nginx khong tim thay file va chuyen xuong PHP thi PHP doc nguoc ra duoc
     * anh goc va kich thuoc can resize. Bam ma thi khong lam dieu do duoc.
     */
    function thumb_cache_path(string $relative, ?int $width, ?int $height): string
    {
        return '/image-cache/' . (int) $width . 'x' . (int) $height . '/' . ltrim($relative, '/');
    }
}

if(!function_exists('getthumb')){
    /**
     * Tra ve URL anh. Co truyen $width/$height thi tra ve URL ban da resize.
     *
     * TRUOC DAY: tra ve /thumb?src=...&w=600 - moi tam anh di qua Laravel, ke
     * ca anh da nam san trong cache. Do duoc tren production: mot anh da cache
     * mat 0,56s vi phai khoi dong ca framework, trong khi nginx phuc vu file
     * tinh chi mat 0,011s. Trang chu co 114 request anh, tuc khoang 65 giay
     * thoi gian worker cho MOT luot xem trang - day la ly do pool php-fpm bao
     * hoa (log ghi 307 lan "server reached max_children").
     *
     * NAY: tra ve duong dan tinh /image-cache/600x0/uploads/images/abc.jpg.
     * File ton tai thi nginx/Apache phuc vu thang, khong dung toi PHP. Chua co
     * thi try_files roi xuong index.php, Laravel resize, ghi file ra dung
     * duong dan do, va tu lan sau tro di web server lo.
     *
     * Khong truyen kich thuoc -> van tra anh goc nhu cu.
     */
    function getthumb($image = null, $width = null, $height = null){
        $url = \App\Support\LegacyFrontend::image($image);

        if (empty($width) && empty($height)) {
            return $url;
        }

        // Chi resize duoc anh noi bo. Anh dat tren domain khac thi tra nguyen.
        $base = rtrim(asset('/'), '/');
        if (!str_starts_with($url, $base)) {
            return $url;
        }

        $relative = substr($url, strlen($base));
        $relative = '/' . ltrim(strtok($relative, '?#') ?: '', '/');

        // Anh du phong no-image thi khong can qua resizer.
        if (str_contains($relative, 'no-image')) {
            return $url;
        }

        return url(thumb_cache_path($relative, (int) $width, (int) $height));
    }
}

if(!function_exists('thumb_srcset')){
    /**
     * Sinh chuoi srcset cho mot anh: moi be rong mot ban resize qua /thumb.
     *
     * Dung cho anh co khung thay doi theo be rong man hinh (anh full-bleed,
     * the trong luoi). Truoc day chi co mot ban goc 1920px: dien thoai rong
     * 390px cung phai tai dung anh do. Voi srcset trinh duyet tu chon ban vua
     * khung, tiet kiem phan lon dung luong tren mobile.
     *
     * Tra ve chuoi rong neu anh khong resize duoc (anh ngoai domain, no-image)
     * - luc do view chi dung src nhu cu, khong hong gi.
     */
    function thumb_srcset($image = null, array $widths = []){
        if (empty($widths)) {
            return '';
        }

        $parts = [];
        foreach (array_unique($widths) as $w) {
            $w = (int) $w;
            if ($w <= 0) { continue; }

            $url = getthumb($image, $w);
            // getthumb tra nguyen URL khi khong resize duoc -> khong co /image-cache/.
            if (!str_contains($url, '/image-cache/')) { return ''; }

            $parts[] = $url . ' ' . $w . 'w';
        }

        return implode(', ', $parts);
    }
}

if(!function_exists('navigations_array')){
    function navigations_array($position = 'main', $language = null){
        return \App\Support\LegacyFrontend::navigations($position, $language ?: 1);
    }
}

if(!function_exists('getSystem')){
    function getSystem($key = '', $default = ''){
        $system = view()->shared('system') ?? [];
        return \App\Support\LegacyFrontend::system(is_array($system) ? $system : [], $key, $default);
    }
}

if(!function_exists('show_time')){
    function show_time($time = null, $format = 'd/m/Y'){
        if(empty($time)){
            return '';
        }
        try{
            return \Carbon\Carbon::parse($time)->format($format);
        }catch(\Throwable $th){
            return (string) $time;
        }
    }
}

if(!function_exists('percent')){
    function percent($price = 0, $saleoff = 0){
        if((float) $price <= 0 || (float) $saleoff <= 0){
            return 0;
        }
        return round(((float) $price - (float) $saleoff) / (float) $price * 100);
    }
}

if(!function_exists('seo')){
    /**
     * Gan nhan trang cho tieu de va mo ta khi $page > 1.
     *
     * Truoc day moi trang phan trang dung Y HET tieu de va mo ta cua trang 1 -
     * chi khac canonical. Search Console bao 44 trang "Trang trung lap, Google
     * da chon mot trang chinh tac khac", phan lon la cac duong
     * /{danh-muc}/trang-2.html ... /trang-13.html: noi dung the hien ra giong
     * nhau nen Google gop het ve trang 1.
     *
     * Them " - Trang N" la cach chuan de phan biet. Khong dung rel=next/prev
     * vi Google da ngung ho tro tu 2019.
     */
    function seo($model = null, $page = 1){
        $page = max(1, (int) $page);
        $canonical = ($page > 1) ? write_url($model->canonical, true, false).'/trang-'.$page.config('apps.general.suffix'): write_url($model->canonical, true, true);

        $title       = ($model->meta_title) ?? $model->name;
        $description = ($model->meta_description) ?? cut_string_and_decode($model->descipriont, 168);

        if ($page > 1) {
            $title       = $title . ' - Trang ' . $page;
            $description = $description . ' (Trang ' . $page . ')';
        }

        return [
            'meta_title' => $title,
            'meta_keyword' => ($model->meta_keyword) ?? '',
            'meta_description' => $description,
            'meta_image' => $model->image,
            'canonical' => $canonical,
        ];
    }
}

if(!function_exists('recursive')){
    function recursive($data, $parentId = 0){
        $temp = [];
        if(!is_null($data) && count($data)){
            foreach($data as $key => $val){
                if($val->parent_id == $parentId){
                    $temp[] = [
                        'item' => $val,
                        'children' => recursive($data, $val->id)
                    ];
                }
            }
        }
        return $temp;
    }
}

if(!function_exists('frontend_recursive_menu')){
    function frontend_recursive_menu(array $data = [], int $parentId = 0, int $count = 1, $type = 'html'){
        $html = '';
        if(isset($data) && !is_null($data) && count($data)){
            if($type == 'html'){
                foreach($data as $key => $val){
                    $name = $val['item']->languages->first()->pivot->name;
                    $canonical = write_url($val['item']->languages->first()->pivot->canonical, true, true);
                    $ulClass = ($count >= 1) ? 'menu-level__'.($count + 1) : '';
                    
                    $isHome = ($name == 'Trang chủ' || $name == 'Home') && (url()->current() == url('/'));
                    $isActive = ($isHome || url()->current() == $canonical) ? 'active' : '';
                    
                    $liClasses = [];
                    if ($count != 0 && count($val['children'])) {
                        $liClasses[] = 'children';
                    }
                    if ($isActive) {
                        $liClasses[] = 'active';
                    }
                    $liClassStr = count($liClasses) ? implode(' ', $liClasses) : '';
                    
                    $html .= '<li class="'.$liClassStr.'">';
                        $html .= '<a href="'.(($name == 'Trang chủ' || $name == 'Home') ? '.' : $canonical).'" title="'.$name.'" data-menu-id="'.$val['item']->id.'">'.
                        (($name == 'Home') ? '' : '').$name.'</a>';
                        if(count($val['children'])){
                            $html .= '<div class="dropdown-menu">';
                                $html .= '<ul class="uk-list uk-clearfix menu-style '.$ulClass.'">';
                                    $html .= frontend_recursive_menu($val['children'], $val['item']->parent_id,  $count + 1, $type);
                                $html .= '</ul>';
                            $html .='</div>';
                        }
                    $html .= '</li>';
                }
                return $html;
            } 
        }
        return $data;
       
    }
}


if(!function_exists('menu_translation_pivot')){
    function menu_translation_pivot($menu){
        $languageId = config('app.language_id', 1);
        $translation = $menu->languages->firstWhere('id', $languageId)
            ?? $menu->languages->first();

        return $translation?->pivot;
    }
}

if(!function_exists('recursive_menu')){
    function recursive_menu($data){
        $html = '';
        if(count($data)){
            foreach($data as $key => $val){
                $itemId = $val['item']->id;
                $itemName = menu_translation_pivot($val['item'])?->name ?? '(Chưa dịch)';
                $itemUrl = route('menu.children', ['id' => $itemId]);


                $html .= "<li class='dd-item' data-id='$itemId'>" ;
                    $html .= "<div class='dd-handle'>";
                        $html .= "<span class='label label-info'><i class='fa fa-arrows'></i></span> $itemName";
                    $html .= "</div>";
                    $html .= "<a class='create-children-menu' href='$itemUrl'> Quản lý menu con </a>";

                    if(count($val['children'])){
                        $html .= "<ol class='dd-list'>";
                            $html .= recursive_menu($val['children']);
                        $html .= '</ol>';
                    }
                $html .= "</li>";
            }
        }
        return $html;
    }
}


if(!function_exists('buildMenu')){
    function buildMenu($menus = null, $parent_id = 0, $prefix = ''){
        $output = [];
        $count = 1;

        if(count($menus)){
            foreach($menus as $key => $val){
                if($val->parent_id == $parent_id){
                    $val->position = $prefix.$count;
                    $output[] = $val;
                    $output = array_merge($output, buildMenu($menus, $val->id, $val->position . '.'));
                    $count++;
                }
            }
        }
        return $output;
    }
}

use Illuminate\Support\Str;
if(!function_exists('loadClass')){
    function loadClass(string $model = '', $folder = 'Repositories', $interface = 'Repository'){
        $serviceInstance = null;
        $namespace = Str::words(Str::headline($model), 1, '');
        $version2 = ['Scholar', 'School', 'Major', 'Admission'];
        if(in_array($namespace, $version2)){
            $interface = 'Repo';
        }
        // $serviceInterfaceNamespace = '\App\\'.$folder.'\\' . ucfirst($model) . $interface;
        $serviceInterfaceNamespace = '\App\\' . $folder . '\\' . $namespace . '\\' . $model . $interface;
        if (class_exists($serviceInterfaceNamespace)) {
            $serviceInstance = app($serviceInterfaceNamespace);
        }
        return $serviceInstance;
    }
}

if(!function_exists('convertArrayByKey')){
    function convertArrayByKey($object = null, $fields = []){
        $temp = [];
        foreach($object as $key => $val){
            foreach($fields as $field){
                if(is_array($object)){
                    $temp[$field][] = $val[$field];
                }else{
                    $extract = explode('.', $field);
                    if(count($extract) == 2){
                        if($extract[1] == 'languages'){
                            $temp[$extract[0]][] = $val->{$extract[1]}->first()->pivot->{$extract[0]};
                        }else{
                            $temp[$extract[0]][] = $val->pivot->{$extract[0]};
                        }
                        
                    }else{
                        $temp[$field][] = $val->{$field}; 
                    }
                    
                }
            }
        }
        return $temp;
    }
}

if(!function_exists('renderQuickBuy')){
    function renderQuickBuy($product, string $canonical = '', string $name = ''){

        $class = 'btn-addCart';
        $openModal = '';
        if(isset($product->product_variants) && count($product->product_variants)){
            $class = '';
            $canonical = '#popup';
            $openModal = 'data-uk-modal';
        }

        $html = '<a href="'.$canonical.'" '.$openModal.' title="'.$name.'" class="'.$class.'">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g>
                    <path d="M24.4941 3.36652H4.73614L4.69414 3.01552C4.60819 2.28593 4.25753 1.61325 3.70863 1.12499C3.15974 0.636739 2.45077 0.366858 1.71614 0.366516L0.494141 0.366516V2.36652H1.71614C1.96107 2.36655 2.19748 2.45647 2.38051 2.61923C2.56355 2.78199 2.68048 3.00626 2.70914 3.24952L4.29414 16.7175C4.38009 17.4471 4.73076 18.1198 5.27965 18.608C5.82855 19.0963 6.53751 19.3662 7.27214 19.3665H20.4941V17.3665H7.27214C7.02705 17.3665 6.79052 17.2764 6.60747 17.1134C6.42441 16.9505 6.30757 16.7259 6.27914 16.4825L6.14814 15.3665H22.3301L24.4941 3.36652ZM20.6581 13.3665H5.91314L4.97214 5.36652H22.1011L20.6581 13.3665Z" fill="#253D4E"></path>
                    <path d="M7.49414 24.3665C8.59871 24.3665 9.49414 23.4711 9.49414 22.3665C9.49414 21.2619 8.59871 20.3665 7.49414 20.3665C6.38957 20.3665 5.49414 21.2619 5.49414 22.3665C5.49414 23.4711 6.38957 24.3665 7.49414 24.3665Z" fill="#253D4E"></path>
                    <path d="M17.4941 24.3665C18.5987 24.3665 19.4941 23.4711 19.4941 22.3665C19.4941 21.2619 18.5987 20.3665 17.4941 20.3665C16.3896 20.3665 15.4941 21.2619 15.4941 22.3665C15.4941 23.4711 16.3896 24.3665 17.4941 24.3665Z" fill="#253D4E"></path>
                    </g>
                    <defs>
                    <clipPath>
                    <rect width="24" height="24" fill="white" transform="translate(0.494141 0.366516)"></rect>
                    </clipPath>
                    </defs>
                </svg>
        </a>';
    return $html;
    }
}

if(!function_exists('cutnchar')){
	function cutnchar($str = NULL, $n = 320){
		// Dung mb_* thay cho strlen/substr: noi dung la tieng Viet UTF-8 nen
		// mot ky tu co dau chiem 2-3 byte. Ban cu dem theo byte, tuc $n = 220
		// chi ra khoang 150 ky tu that, va co the cat doi mot ky tu nhieu byte.
		$str = (string) $str;
		if (mb_strlen($str) < $n) return $str;
		$html = mb_substr($str, 0, $n);
		$pos = mb_strrpos($html, ' ');
		// Neu khong co dau cach ASCII nao trong doan da cat thi giu nguyen doan
		// do. Ban cu goi substr($html, 0, false) -> chuoi rong, mo ta chi con
		// dau "...". Chuyen nay xay ra khi chuoi mo dau bang &nbsp; da decode
		// thanh U+00A0 - khoang trang khong ngat, khong phai dau cach ASCII.
		if ($pos !== false) {
			$html = mb_substr($html, 0, $pos);
		}
		return $html.'...';
	}
}

if(!function_exists('cut_string_and_decode')){
	/**
	 * Lay mo ta ngan tu HTML do trinh soan thao sinh ra.
	 *
	 * Phai decode entity TRUOC khi bo tag va cat, vi hai ly do:
	 *  - Blade {{ }} se escape dau & mot lan nua, nen &nbsp; trong DB ra
	 *    &amp;nbsp; va trang hien dung chu "&nbsp;" thay vi khoang trang.
	 *  - Do dai dem sau khi decode moi dung: "&aacute;" la 8 ky tu truoc khi
	 *    decode nhung chi la 1 ky tu ("a") sau khi decode.
	 */
	function cut_string_and_decode($str = NULL, $n = 200){
        $str = html_entity_decode((string) $str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $str = strip_tags($str);
        // &nbsp; decode ra U+00A0 chu khong phai dau cach ASCII. Mo ta trong
        // DB thuong mo dau bang vai cai &nbsp; nen phai gom khoang trang lai,
        // neu khong excerpt bi thut dau dong va cutnchar khong tim duoc cho cat.
        $str = preg_replace('/[\s\x{00A0}]+/u', ' ', $str);
        $str = trim($str);
        $str = cutnchar($str, $n);
        return $str;
	}
}

if(!function_exists('categorySelectRaw')){
    function categorySelectRaw($table = 'products'){
        $rawQuery = "
            (
                SELECT COUNT(id) 
                FROM {$table}s
                JOIN {$table}_catalogue_{$table} as tb3 ON tb3.{$table}_id = {$table}s.id
                WHERE tb3.{$table}_catalogue_id IN (
                    SELECT id
                    FROM {$table}_catalogues as parent_category
                    WHERE lft >= (SELECT lft FROM {$table}_catalogues as pc WHERE pc.id = {$table}_catalogues.id)
                    AND rgt <= (SELECT rgt FROM {$table}_catalogues as pc WHERE pc.id = {$table}_catalogues.id)
                )
            ) as {$table}s_count 
        "; 
        return $rawQuery;
    }
}


if(!function_exists('sortString')){
    function sortString($string = ''){
        $extract = explode(',', $string);
        $extract = array_map('trim', $extract);
        sort($extract, SORT_NUMERIC);
        $newArray = implode(',', $extract);
        return $newArray;
    }
}


if(!function_exists('sortAttributeId')){
    function sortAttributeId(array $attributeId = []){
        sort($attributeId, SORT_NUMERIC);
        $attributeId = implode(',', $attributeId);
        return $attributeId;
    }
}


if(!function_exists('vnpayConfig')){
    function vnpayConfig(){
        return [
            'vnp_Url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'vnp_Returnurl' => write_url('return/vnpay'),
            'vnp_TmnCode' => 'RLE42FCR',
            'vnp_HashSecret' => 'OQPUUZRVSSJASOQVUQHHURHBXGDIMBTU',
            'vnp_apiUrl' => 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html',
            'apiUrl' => 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'
        ];
    }
}


if(!function_exists('momoConfig')){
    function momoConfig(){
        return [
            'partnerCode' => 'MOMOBKUN20180529',
            'accessKey' => 'klm05TvNBzhg7h7j',
            'secretKey' => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
        ];
    }
}

if(!function_exists('zaloConfig')){
    function zaloConfig(){
        return [
            'appid' => '553',
            'key1' => '9phuAOYhan4urywHTh0ndEXiV3pKHr5Q',
            'key2' => 'Iyz2habzyr7AG8SgvoBCbKwKi3UzlLi3',
        ];
    }
}

if(!function_exists('execPostRequest')){
    function execPostRequest($url, $data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
}


if(!function_exists('getReviewName')){
    function getReviewName($string){
        // $string = Nguyễn Công Tuấn
        $words = explode(' ', $string);
        $initialize = '';
        foreach($words as $key => $val){
            $initialize .= strtoupper(substr($val, 0, 1));
        }
        return $initialize;
    }
}


if(!function_exists('generateStar')){
    function generateStar($rating){
        $rating = max(1, min(5, $rating));
        $output = '<div class="review-star">';
            for($i = 1; $i <= $rating; $i++){
                $output .= '<i class="fa fa-star"></i>';
            }
            for($i = $rating + 1; $i <= 5; $i++){
                $output .= '<i class="fa fa-star-o"></i>';
            }
        $output .= '</div>';
        return $output;
    }
}


if(!function_exists('convertCombineArray')){
    function convertCombineArray(mixed $data, $mix_1 = ''){
        $array = [];
        foreach($data as $key => $val){
            $array[$val->id] = (($mix_1 != '') ? $val->{$mix_1} : $val->code).' / '.$val->phone;
        }
        return $array;
    }
}


if(!function_exists('convertArray')){
    function convertArray($datas){
        $id = [];
        foreach ($datas as $data) {
            $id[]= $data->id;
        }
        return $id;
    }
}

if(!function_exists('convertToIdNameArray')){
    function convertToIdNameArray($customers)
   {
    $idNameArray = [];

    foreach ($customers as $customer) {
        $idNameArray[$customer['id']] = $customer['name'];
    }

    return $idNameArray;
    }

}

if(!function_exists('convertToK')){
    function convertToK($discount)
   {
        if ($discount >= 1000) {
            return number_format($discount / 1000, 0, '.', '') . 'k';
        }
        return $discount;
    }
}
  
use Illuminate\Support\Facades\DB;
if(!function_exists('convertData')){
    function convertData($data, $type)
    {
        $promotion_id = $data->id;
        $payload_pivot = ($type == 'products') ? $data->promotion_rules : $data->promotion_gifts;
        $products = ($type == 'products') ? 
        DB::table('promotion_rules')->where('promotion_id', $promotion_id)->get() : DB::table('promotion_gifts')->where('promotion_id', $promotion_id)->get();
        $temp = [];
        if(!is_null($products)){
            foreach($products as $k => $v){
                $temp['id'][$k] = $v->product_id;
                $temp['quantity'][$k] = $v->quantity;
                $temp['image'][$k] = $payload_pivot[$k]['image'];
                $temp['name'][$k] = $payload_pivot[$k]->languages->first()->pivot->name;
            }
        }
        return $temp;
    }
}



if(!function_exists('thumb')){
    function thumb($path, $width = null, $height = null)
    {
        $width = 600;
        $height = 400;

        if (empty($path)) {
            return asset('images/no-image.jpg');
        }
        
        $params = ['src' => $path];
        
        if ($width) {
            $params['w'] = $width;
        }
        
        if ($height) {
            $params['h'] = $height;
        }
        
        // return route('thumb', $params);

        return $path;
    }
}

if (!function_exists('convertImgToAnchor')) {
    function convertImgToAnchor($html) {
        if (!$html || !is_string($html)) {
            return $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $images = $dom->getElementsByTagName('img');
        if ($images->length === 0) {
            return $html;
        }

        foreach ($images as $image) {
            $src = $image->getAttribute('src') ?: '';
            $alt = $image->getAttribute('alt') ?: '';
            $class = $image->getAttribute('class') ?: 'img-cover img-zoomin';

            // Tạo thẻ <a>
            $anchor = $dom->createElement('a');
            $anchor->setAttribute('href', $src);
            $anchor->setAttribute('title', $alt);
            $anchor->setAttribute('class', $class);

            // Thêm <div class="skeleton-loading"> vào trong <a>
            $skeleton = $dom->createElement('span');
            $skeleton->setAttribute('class', 'skeleton-loading');
            $anchor->appendChild($skeleton);

            // Thêm <img class="lazy-image"> vào trong <a>
            $newImg = $dom->createElement('img');
            $newImg->setAttribute('class', 'lazy-image');
            $newImg->setAttribute('data-src', $src);
            $newImg->setAttribute('alt', $alt);
            $anchor->appendChild($newImg);

            // Thay thế <img> bằng <a> hoàn chỉnh
            $image->parentNode->replaceChild($anchor, $image);
        }

        $html = $dom->saveHTML();
        // Loại bỏ các thẻ HTML bổ sung do DOMDocument thêm vào
        $html = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(['<html><body>', '</body></html>'], '', $html));

        return $html;
    }
}

if (!function_exists('calculateCourses')) {
    function calculateCourses($product) {
        $totalMinutes = 0;
        $totalSession = 0;
        $temp = $product->chapter;
        if (!is_array($temp)) {
            $temp = json_decode($temp, true); 
        }
        foreach ($temp as $chapter) {
            if (isset($chapter['content']) && is_array($chapter['content'])) {
                foreach ($chapter['content'] as $lesson) {
                    $totalSession++;
                    if (isset($lesson['time'])) {
                        $totalMinutes += (int)$lesson['time'];
                    }
                }
            }
        }
        
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        $durationText = '';
        if ($hours > 0 && $minutes > 0) {
            $durationText = $hours . ' giờ ' . $minutes . ' phút';
        } elseif ($hours > 0) {
            $durationText = $hours . ' giờ';
        } else {
            $durationText = $minutes . ' phút';
        }
        $chapters = [
            'durationText' => $durationText,
            'totalSession' => $totalSession
        ];
        return $chapters;
    }
}

if(!function_exists('company_name')){
    /**
     * Ten cong ty / thuong hieu, lay tu Cau hinh > He thong trong admin.
     *
     * Dung cho nhung noi KHONG co san $this->system: mail (chay trong queue,
     * khong co request), view backend (composer chi chia se 'system' cho
     * frontend.* va mobile.*)...
     *
     * Doc theo dung khoa cache ma FrontendController dung, nen khi admin luu
     * cau hinh thi SystemService::save() xoa cache la cho nay cung doi theo.
     *
     * Thu tu uu tien:
     *   homepage_company   - ten phap nhan
     *   homepage_brandname - ten thuong hieu, dung khi chua dat ten cong ty
     *   config('app.name') - luoi cuoi, de khong bao gio tra ve chuoi rong
     */
    function company_name($languageId = 1){
        try {
            $system = \Illuminate\Support\Facades\Cache::remember(
                \App\Http\Controllers\FrontendController::systemCacheKey((int) $languageId),
                3600,
                fn () => convert_array(
                    \App\Models\System::where('language_id', $languageId)->get(),
                    'keyword',
                    'content'
                )
            );
        } catch (\Throwable $e) {
            report($e);
            $system = [];
        }

        foreach (['homepage_company', 'homepage_brandname'] as $key) {
            $value = trim((string) ($system[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return (string) config('app.name');
    }
}

if(!function_exists('image_file_exists')){
    /**
     * Anh nay co that tren dia khong?
     *
     * PHAI giai ma URL truoc khi hoi he thong tap tin. CSDL luu duong dan da
     * ma hoa URL - anh upload qua trinh soan thao duoc luu kieu
     *
     *     /userfiles/image/123/thiet-ke-karaoke...azktv%20(1).jpg
     *
     * trong khi ten file that tren dia co DAU CACH. file_exists() doc dia chu
     * khong doc URL, nen dua thang chuoi %20 vao la no di tim mot file ten
     * '...azktv%20(1).jpg' va khong bao gio thay.
     *
     * Hau qua that: tren trang chu, hai the trong khoi "Thi cong karaoke" rot
     * xuong anh du phong, ma anh du phong luc do cung khong ton tai - thanh ra
     * o vo anh. Cung bai do o trang danh muc lai hien binh thuong, vi duong do
     * di qua getthumb() khong he kiem tra su ton tai.
     */
    function image_file_exists(?string $path): bool
    {
        $path = trim((string) $path);

        if ($path === '') {
            return false;
        }

        // Anh dat tren domain khac thi khong kiem tra duoc, coi nhu co.
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
            return true;
        }

        // Giai ma xong phai chot lai bang realpath.
        //
        // rawurldecode bien ca '%2e%2e%2f' thanh '../', nen neu chi ghep chuoi
        // roi hoi is_file thi mot duong dan nhu '/%2e%2e%2f.env' se tra ve
        // true - dung cai bay path traversal ma ImageResizerController da phai
        // va mot lan.
        $that = realpath(public_path(rawurldecode(ltrim($path, '/'))));
        $goc = realpath(public_path());

        if ($that === false || $goc === false) {
            return false;
        }

        return str_starts_with($that, $goc . DIRECTORY_SEPARATOR) && is_file($that);
    }
}

if(!function_exists('image_or_fallback')){
    /**
     * Duong dan anh, hoac anh thay the neu thieu file.
     *
     * Truoc day doan nay duoc chep tay vao sau view, moi ban deu kem mot danh
     * sach bon anh du phong ghi cung - ma ca bon deu khong con tren may chu.
     * Gom ve mot cho de lan sau sua mot lan la xong, va de mac dinh tro vao
     * /images/no-image.jpg (co that).
     */
    function image_or_fallback(?string $path, ?string $fallback = null): string
    {
        $fallback = $fallback ?: '/images/no-image.jpg';

        if (!image_file_exists($path)) {
            return asset(ltrim($fallback, '/'));
        }

        $path = trim((string) $path);

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])
            ? $path
            : asset(ltrim($path, '/'));
    }
}

if(!function_exists('optimize_content_images')){
    /**
     * Toi uu cac the <img> do bien tap vien dan thang vao noi dung qua CKEditor.
     *
     * VAN DE
     *     Anh trong than bai viet duoc dan thang dang
     *         <img src="/uploads/images/abc.jpg" style="width: 700px; height: 525px;">
     *     tuc la lay THANG file goc tren dia, khong di qua /image-cache/. Do
     *     duoc tren trang loi bai hat - 374 trong so 435 URL ma Search Console
     *     dang bao "LCP lau hon 2,5s":
     *         - 2.039 KB anh moi trang, trong khi trang chu chi 310 KB
     *         - 19/28 anh khong co loading=lazy
     *         -  3/28 anh co width+height  -> nhay layout (CLS)
     *         - anh nang nhat 297 KB JPEG; qua image-cache con 37 KB WebP (-88%)
     *
     * CACH LAM: CHI sua dung may thuoc tinh can sua tren tung the <img>, khong
     * phan tich lai ca khoi HTML. Noi dung CKEditor thuong khong chuan (co ca
     * the <html><body> lot ben trong). Neu nap bang DOMDocument roi xuat lai
     * thi moi ky tu tieng Viet bi doi thanh ma so, lam phinh HTML, va ca khoi
     * noi dung bi viet lai - rui ro lon hon nhieu so voi cai duoc.
     *
     *     - src  -> /image-cache/{w}x0/... de lay ban WebP da nen
     *     - width/height theo kich thuoc that de trinh duyet chua cho san.
     *       Thuoc tinh cua the YEU HON moi quy tac CSS, nen giao dien khong doi;
     *       no chi cung cap ty le khung hinh truoc khi anh tai xong.
     *     - anh dau tien: fetchpriority=high (thuong chinh la phan tu LCP)
     *     - cac anh con lai: loading=lazy
     *
     * KHONG dung cache: getimagesize() chi doc phan dau file, vai phan muoi
     * mili giay moi anh - khong dang ke so voi ~900ms khoi dong framework.
     */
    function optimize_content_images(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        // Noi dung CKEditor doi khi chua nguyen ca <html><body> - day la mot
        // trong hai nguyen nhan khien moi trang co 2-3 the <body>.
        $html = preg_replace('#<!DOCTYPE[^>]*>#i', '', $html);
        $html = preg_replace('#</?(?:html|body|head)\b[^>]*>#i', '', $html);

        $index = 0;

        $out = preg_replace_callback('#<img\b[^>]*>#i', function ($m) use (&$index) {
            $tag = $m[0];

            if (!preg_match('#\bsrc\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))#i', $tag, $s)) {
                return $tag;
            }

            $src = $s[2] ?? '';
            if ($src === '') { $src = $s[3] ?? ''; }
            if ($src === '') { $src = $s[4] ?? ''; }
            $src = trim($src);

            if ($src === '' || stripos($src, 'data:') === 0) {
                return $tag;
            }

            // Anh dat tren domain khac thi khong resize duoc, de nguyen.
            $base = rtrim(asset('/'), '/');
            if (preg_match('#^(https?:)?//#i', $src)) {
                if (strpos($src, $base) !== 0) {
                    return $tag;
                }
                $src = substr($src, strlen($base));
            }

            // Giu ban da ma hoa URL de dung lam duong dan, va ban da giai ma
            // de tra cuu file tren dia.
            $relUrl = '/' . ltrim(strtok($src, '?#') ?: '', '/');
            if (stripos($relUrl, '/image-cache/') === 0 || strpos($relUrl, '..') !== false) {
                return $tag;
            }

            $relFs = urldecode($relUrl);
            $first = strtolower(explode('/', ltrim($relFs, '/'))[0] ?? '');
            if (!in_array($first, ['uploads', 'userfiles', 'images', 'templates'], true)) {
                return $tag;
            }

            $ext = strtolower(pathinfo($relFs, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                return $tag;
            }

            $full = public_path(ltrim($relFs, '/'));
            if (!is_file($full)) {
                return $tag;
            }

            $size = @getimagesize($full);
            if (!$size || empty($size[0]) || empty($size[1])) {
                return $tag;
            }

            $ow = (int) $size[0];
            $oh = (int) $size[1];

            // Khung noi dung rong khoang 925px tren may tinh. Khong bao gio
            // phong to anh goc.
            $tw = min($ow, 1000);
            $th = max(1, (int) round($oh * ($tw / $ow)));

            $index++;

            foreach (['src', 'srcset', 'sizes', 'width', 'height',
                      'loading', 'decoding', 'fetchpriority'] as $attr) {
                $tag = preg_replace(
                    '#\s+' . $attr . '\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $tag
                );
            }
            $tag = preg_replace('#\s*/?>$#', '', $tag);

            // Anh dau tien thuong la phan tu LCP: khong hoan tai, uu tien cao.
            $prio = $index === 1 ? ' fetchpriority="high"' : ' loading="lazy"';

            return $tag
                . ' src="' . htmlspecialchars(url(thumb_cache_path($relUrl, $tw, 0)), ENT_QUOTES) . '"'
                . ' width="' . $tw . '" height="' . $th . '"'
                . ' decoding="async"' . $prio . '>';
        }, $html);

        return $out === null ? $html : $out;
    }
}
