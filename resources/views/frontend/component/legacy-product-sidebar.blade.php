@php
    $featuredPosts = \App\Support\LegacyFrontend::featuredSidebarPosts($config['language'] ?? 1);
    $supportGroups = \App\Support\LegacyFrontend::supportGroups();
@endphp
<aside class="aside aside-legacy-product">
    <section class="aside-panel aside-register">
        <div class="panel-body">
            <div class="register-title">Đăng ký để nhận các thông tin mới nhất từ Vinaco.</div>
            <form class="uk-form register-form" action="#" method="post">
                <input type="email" name="email" placeholder="Email của bạn">
                <button type="submit">Gửi</button>
            </form>
        </div>
    </section>

    @if(count($featuredPosts))
        <section class="aside-panel aside-featured-news">
            <header class="panel-head"><div class="heading-3"><span>Tin tức nổi bật</span></div></header>
            <section class="panel-body">
                @foreach($featuredPosts as $post)
                    @php
                        $title = $post['title'] ?? '';
                        $href = rewrite_url($post['canonical'] ?? '');
                        $image = getthumb($post['images'] ?? null);
                    @endphp
                    <article class="featured-news-item uk-clearfix">
                        <a class="thumb img-cover" href="{{ $href }}" title="{{ $title }}"><img src="{{ $image }}" alt="{{ $title }}"></a>
                        <h3 class="title"><a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a></h3>
                    </article>
                @endforeach
            </section>
        </section>
    @endif

    @if(count($supportGroups))
        <section class="aside-panel aside-support-online">
            <header class="panel-head"><div class="heading-3"><span>Hỗ trợ trực tuyến</span></div></header>
            <section class="panel-body">
                @foreach($supportGroups as $group)
                    <div class="support-group">
                        <div class="support-title">{{ $group['title'] }}</div>
                        @foreach($group['items'] as $support)
                            <div class="support-person">{{ $support['name'] }} : {{ $support['phone'] }}</div>
                        @endforeach
                    </div>
                @endforeach
            </section>
        </section>
    @endif
</aside>

<style>
    .aside-legacy-product .aside-panel{border:1px solid #ddd;margin-bottom:20px;background:#fff}
    .aside-legacy-product .panel-body{padding:14px}
    .aside-legacy-product .register-title{font-size:14px;line-height:20px;margin-bottom:12px}
    .aside-legacy-product .register-form{display:flex}
    .aside-legacy-product .register-form input{width:100%;height:33px;border:0;background:#eee;padding:0 12px;font-style:italic}
    .aside-legacy-product .register-form button{height:33px;border:0;background:#00a651;color:#fff;font-weight:bold;padding:0 14px}
    .aside-legacy-product .panel-head{padding:10px 14px 0}
    .aside-legacy-product .heading-3{border-bottom:1px dotted #ccc;color:#00a651;font-size:16px;font-weight:bold;text-transform:uppercase}
    .aside-legacy-product .heading-3 span{display:inline-block;border-bottom:2px solid #ff7a00;padding-bottom:6px}
    .featured-news-item{border-bottom:1px solid #eee;padding:14px 0;min-height:96px}
    .featured-news-item:last-child{border-bottom:0}
    .featured-news-item .thumb{float:left;width:68px;height:54px;margin-right:12px}
    .featured-news-item .title{font-size:14px;line-height:20px;margin:0;font-weight:bold}
    .featured-news-item .title a{color:#000}
    .aside-support-online .panel-body{padding-top:0}
    .support-group{border-bottom:1px solid #eee;padding:14px 0}
    .support-group:last-child{border-bottom:0}
    .support-title{font-size:13px;font-weight:bold;margin-bottom:8px}
    .support-person{font-size:16px;font-weight:bold;color:#000}
</style>
