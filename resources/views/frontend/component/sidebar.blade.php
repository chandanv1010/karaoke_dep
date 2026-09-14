<!-- ASIDE PRODUCT -->
<aside class="aside">
    {{-- DANH MỤC SẢN PHẨM --}}
    @php
        $category = \App\Models\ProductCatalogue::where('publish', 2)
            ->orderByDesc('order')
            ->orderByDesc('id')
            ->with(['languages'])
            ->get();

        $category = recursive($category);
        
        // dd($category);
    @endphp

    @if(!empty($category))
        @foreach($category as $val)
        @php
            $catName = $val['item']->languages->first()->pivot->name;
        @endphp
            <section class="aside-category aside-panel">
                <div class="aside-heading"><span>{{ $catName }}</span></div>
                <div class="aside-body">
                    @if(!empty($val['children']))
                        <ul class="uk-list uk-clearfix list-cat">
                            @foreach($val['children'] as $valChildren)
                                @php
                                    $name = $valChildren['item']->languages->first()->pivot->name;
                                    $canonical = write_url($valChildren['item']->languages->first()->pivot->canonical);
                                @endphp
                                <li>
                                    <a href="{{ $canonical }}" title="{{ $name }}" class="uk-position-relative">
                                        <span>{{ $name }}</span>
                                        @if(!empty($valChildren['children']))
                                            <span class="btn_dropdown_sp">
                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                            </span>
                                        @endif
                                    </a>

                                    @if(!empty($valChildren['children']))
                                        <ul class="uk-clearfix children" style="display:none">
                                            @foreach($valChildren['children'] as $valS)
                                             @php
                                                $nameS = $valS['item']->languages->first()->pivot->name;
                                                $canonicalS = write_url($valS['item']->languages->first()->pivot->canonical);
                                            @endphp
                                                <li>
                                                    <a href="{{ $canonicalS }}" title="{{ $nameS }}">{{ $nameS }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endforeach
    @endif
    {{-- Khoi nay truoc day lay hai anh tu https://vinahome.vn (website cua don vi
         khac) va ghi cung so hotline 0968643487 - khong phai so cua cong ty, trong
         khi so that nam o Cau hinh > He thong (contact_hotline).
         Da bo hai anh ngoai: phu thuoc vao may chu cua ben thu ba, ho doi/xoa file
         la vo anh, va moi luot xem deu gui referrer sang do. --}}
    @php $hotline = trim((string) ($system['contact_hotline'] ?? '')); @endphp
    @if($hotline !== '')
    <div class="support">
        <div class="hotline">
            <div class="nav-icon">
                <span class="sp1">Hotline</span>
                <span class="sp2"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}">{{ $hotline }}</a></span>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    @endif
</aside>

{{-- SCRIPT DROPDOWN --}}
<script>
	// jQuery tai bang defer nen $ chua ton tai luc parse -> cho DOMContentLoaded.
	document.addEventListener('DOMContentLoaded', function() {
		$(document).on('click', '.btn_dropdown_sp', function(event) {
			event.preventDefault();
			$(this).toggleClass('active');
			$(this).closest('li').find('.children').slideToggle();
			return false;
		});
	});		
</script>    
