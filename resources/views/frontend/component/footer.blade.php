@php
    $footerMenu = navigations_array('footer', $config['language'] ?? 1);
@endphp
<footer class="footer-new" id="footer">
    <div class="uk-container uk-container-center">
        <div class="container">
            <div class="col">
                <div class="col-title">VỀ
                    {{ mb_strtoupper($system['homepage_brandname'] ?? 'THIẾT KẾ THI CÔNG KARAOKE AZKTV') }}</div>
                <div class="col-content">
                    {!! $system['common_intro'] ?? '' !!}
                </div>
            </div>

            <div class="col">
                <div class="col-title">{{ mb_strtoupper($system['homepage_company'] ?? 'CTCP GIẢI TRÍ AZKTV VIỆT NAM') }}
                </div>
                <div class="col-content">
                    <ul>
                        <li><i class="fa fa-map-marker"></i>
                            <span><strong>VP:</strong>&nbsp;{{ $system['contact_address'] ?? '' }}</span>
                        </li>
                        <li><i class="fa fa-map-marker"></i>
                            <span><strong>VP2:</strong>&nbsp;{{ $system['contact_address_2'] ?? '' }}</span>
                        </li>
                        <li><i class="fa fa-map-marker"></i>
                            <span><strong>VP3:</strong>&nbsp;{{ explode('-', $system['contact_address_3'] ?? '')[0] ?? '' }}</span>
                        </li>
                        <li><i class="fa fa-desktop"></i> <span><strong>Xưởng sản
                                    xuất:</strong>&nbsp;{{ explode('-', $system['contact_address_3'] ?? '')[1] ?? '' }}</span>
                        </li>
                        <li><i class="fa fa-phone"></i>
                            <span><strong>Hotline:</strong>&nbsp;{{ $system['contact_hotline'] ?? '' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col">
                <div class="col-title">THÔNG TIN</div>
                <div class="col-content">
                    @if (count($footerMenu))
                        <ul>
                            @foreach ($footerMenu as $menu)
                                @if (!empty($menu['items']))
                                    @foreach ($menu['items'] as $item)
                                        <li><a href="{{ $item['href'] }}"
                                                title="{{ $item['title'] }}">{{ $item['title'] }}</a></li>
                                    @endforeach
                                @else
                                    <li><a href="{{ $menu['href'] ?? '#' }}"
                                            title="{{ $menu['title'] }}">{{ $menu['title'] }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="subscribe-section" style="margin-top: 25px;">
                    <div class="col-title footer-new__subscribe-title">ĐĂNG KÍ NHẬN THÔNG TIN</div>
                    <form class="subscribe-form footer-new__subscribe-form">
                        <input type="email" placeholder="Nhập email vào đây" required>
                        <button type="submit" class="footer-new__subscribe-btn"><i
                                class="fa fa-arrow-right"></i></button>
                    </form>
                </div>
            </div>
            <div class="col">
                <div class="col-title">FANPAGE</div>
                <div class="col-content">
                    <a href="{{ $system['seo_facebook'] ?? '#' }}" target="_blank">
                        <img class="footer-new__fanpage-img"
                            src="{{ asset('templates/frontend/resources/img/fanpage.png') }}" alt="Fanpage">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        <div class="container"
            style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 15px; gap: 15px;">
            <div style="flex: 1; text-align: left; min-width: 250px;">Bản quyền thuộc về
                {{ mb_strtoupper($system['homepage_brandname'] ?? 'AZKTV') }} - Website thương mại điện tử đã được Bộ
                Công Thương cấp phép</div>
            <img src="{{ asset('templates/frontend/resources/img/bct.png') }}" alt="Bộ Công Thương"
                style="height: 40px; object-fit: contain;">
        </div>
    </div>
</footer>

<div class="social-icons-float">
    <a href="tel:{{ $system['contact_hotline'] ?? '' }}" class="phone" title="Gọi điện"><i
            class="fa fa-phone"></i></a>
    <a href="https://zalo.me/{{ $system['contact_Zalo'] ?? '' }}" class="zalo" target="_blank" title="Zalo"><i
            class="fa fa-commenting-o"></i></a>
    <a href="{{ $system['seo_facebook'] ?? '#' }}" class="messenger" target="_blank" title="Messenger"><i
            class="fa fa-facebook"></i></a>
</div>
