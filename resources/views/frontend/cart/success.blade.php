@extends('frontend.homepage.layout')

@php
    $orders = $orderSummary['orders'] ?? [];
    $firstOrder = collect($orders)->first();
    $customer = $firstOrder->customer ?? [];
@endphp

@section('content')
<style type="text/css">
.paymentsuccess-2 .payment .step .item{float:left;width:33.33%}.paymentsuccess-2 .payment .step .link{display:block;padding:8px 35px 8px 30px;font-size:13px;line-height:20px;color:#333;font-weight:bold;background:#f0f0f0;position:relative}.paymentsuccess-2 .payment .step .item:first-child .link{padding-left:20px}.paymentsuccess-2 .payment .step .active .link{background:#f4f9fd}.paymentsuccess-2 .payment .step .link:before,.paymentsuccess-2 .payment .step .link:after{content:"";position:absolute;top:50%;transform:translate(0,-50%);border-top:20px solid transparent;border-bottom:20px solid transparent;border-left:13px solid;right:-13px}.paymentsuccess-2 .payment .step .link:before{border-left-color:#fff;right:-14px;z-index:1}.paymentsuccess-2 .payment .step .link:after{z-index:2;border-left-color:#f1f1f1}.paymentsuccess-2 .payment .step .step-3 .link:after{display:none}.paymentsuccess-2 .payment .step .active .link:after{border-left-color:#f4f9fd}.paymentsuccess-2 .payment .step .number{display:inline-block;margin-right:5px;width:24px;height:24px;border-radius:50%;background:#999;color:#fff;text-align:center;font-size:12px;line-height:24px}.paymentsuccess-2 .payment .step .complete .number{text-indent:-999px;background:url({{ asset('templates/backend/images/icon-checked.png') }}) 0 0 no-repeat}.paymentsuccess-2 .payment .step .active .number{background:#0492d5}.paymentsuccess-2 .information .uk-panel,.paymentsuccess-2 .completed{border:1px solid #eee}.paymentsuccess-2 .information .panel-head{padding:10px 15px;background:#f7f7f7;border-bottom:1px solid #eee}.paymentsuccess-2 .information .panel-head .title{font-size:14px;margin:0;color:#333}.paymentsuccess-2 .listorder>.item{padding:15px;overflow:hidden}.paymentsuccess-2 .listorder>.item+.item{border-top:1px dotted #ccc}.paymentsuccess-2 .listorder .colimg{width:80px;margin-right:15px}.paymentsuccess-2 .listorder .colinfo{width:calc(100% - 95px)}.paymentsuccess-2 .listorder .colimg .img{height:80px;border:1px solid #ebebeb}.paymentsuccess-2 .listorder .colinfo .title{font-size:13px;line-height:18px;margin-bottom:10px;width:70%;height:54px;font-weight:bold}.paymentsuccess-2 .listorder .colinfo .link{color:#555}.paymentsuccess-2 .listorder .colinfo .price{font-size:13px;line-height:18px;text-align:right}.paymentsuccess-2 .total{padding:8px 15px;border-top:1px solid #ebebeb}.paymentsuccess-2 .total .tt-price{border-top:1px dashed #ccc;padding-top:15px}.paymentsuccess-2 .total .tt-price .price{color:#d60c0c}.paymentsuccess-2 .completed .heading .text{position:relative;display:inline-block;padding:7px 30px;line-height:24px;font-size:16px;color:#00af1d;font-weight:bold;text-align:center;text-transform:uppercase;background:#fff;border-radius:20px;box-shadow:0 1px 2px 0 rgba(0,0,0,.16);transform:translate(0,-50%)}.paymentsuccess-2 .completed .panel-body{padding:0 20px 20px}.paymentsuccess-2 .infoorder .item{position:relative;padding-left:15px}.paymentsuccess-2 .infoorder .item:before{content:"";display:block;position:absolute;width:5px;height:5px;border-radius:50%;background:#999;left:0;top:7px}.paymentsuccess-2 .infoorder .price span{font-weight:bold;color:#c10017}.paymentsuccess-2 .support .link{font-weight:bold;color:#288ad6}.paymentsuccess-2 .completed .label{padding:5px 10px;text-transform:uppercase;background:#f3f3f3}
</style>

<section class="paymentsuccess-2 mb20">
    <div class="uk-container uk-container-center">
        <div class="payment mtb20">
            <ul class="uk-list uk-clearfix step">
                <li class="item step-1 complete"><a class="link" href="{{ url('/') }}" title="Đặt hàng"><span class="number">1</span> Đặt hàng</a></li>
                <li class="item step-2 complete"><a class="link" href="{{ route('cart.checkout') }}" title="Thông tin giao hàng"><span class="number">2</span> Thông tin giao hàng</a></li>
                <li class="item step-3 active"><a class="link" href="{{ route('cart.success') }}" title="Đặt hàng thành công"><span class="number">3</span> Đặt hàng thành công</a></li>
            </ul>
        </div>
        <div class="uk-grid uk-grid-medium">
            <div class="uk-width-medium-6-10">
                <div class="uk-panel completed mb20">
                    <header class="panel-head">
                        @if(!empty($system['homepage_cover']))
                            <div class="picture ec-cover"><img src="{{ asset($system['homepage_cover']) }}" alt="cover"></div>
                        @endif
                        <h1 class="heading uk-text-center"><span class="text"><i class="fa fa-check"></i> Đặt hàng thành công</span></h1>
                    </header>
                    <div class="panel-body">
                        <div class="thank mb20">Cảm ơn <strong>{{ data_get($customer, 'name', 'Bạn') }}</strong> đã cho {{ $system['homepage_company'] ?? $system['homepage_brandname'] ?? '' }} cơ hội được phục vụ. Nhân viên sẽ liên hệ lại để xác nhận thông tin đặt hàng.</div>
                        <div class="label mb10">Thông tin đặt hàng:</div>
                        <div class="infoorder mb15">
                            <div class="item mb5"><strong>Mã đơn hàng:</strong>
                                @foreach($orders as $key => $order)
                                    {{ $order->code }}{{ $key + 1 < count($orders) ? ', ' : '' }}
                                @endforeach
                            </div>
                            <div class="item mb5"><strong>Tên đầy đủ:</strong> {{ data_get($customer, 'name', '-') }}</div>
                            <div class="item mb5"><strong>Số điện thoại:</strong> {{ data_get($customer, 'phone', '-') }}</div>
                            <div class="item mb5"><strong>Email:</strong> {{ data_get($customer, 'email', '-') }}</div>
                            <div class="item mb5"><strong>Địa chỉ nhận hàng:</strong> {{ data_get($customer, 'address', '-') }}</div>
                            <div class="item mb5"><strong>Ghi chú:</strong> {{ data_get($customer, 'description', '-') }}</div>
                        </div>
                        <div class="support">Khi cần trợ giúp vui lòng gọi <a href="tel:{{ $system['contact_hotline'] ?? '' }}" title="Hotline" class="link">{{ $system['contact_hotline'] ?? '' }}</a> hoặc <a href="tel:{{ $system['contact_phone'] ?? '' }}" title="Điện thoại" class="link">{{ $system['contact_phone'] ?? '' }}</a>.</div>
                    </div>
                </div>
            </div>
            <div class="uk-width-medium-4-10 information">
                @foreach($orders as $order)
                    @php
                        $orderTotal = 0;
                        $discountVoucher = data_get($order, 'cart.cartVoucher', 0);
                        $discountPromotion = data_get($order, 'promotion.discount', 0);
                    @endphp
                    <div class="uk-panel mb20">
                        <header class="panel-head uk-flex uk-flex-middle uk-flex-space-between">
                            <h3 class="title"><span class="text">Đơn hàng <span class="number">({{ data_get($order, 'cart.cartTotalItems', 0) }} sản phẩm)</span></span></h3>
                        </header>
                        <div class="panel-body">
                            <ul class="uk-list listorder">
                                @foreach($order->products as $product)
                                    @php
                                        $qty = (int) data_get($product, 'pivot.qty', 1);
                                        $price = (float) data_get($product, 'pivot.price', 0);
                                        $orderTotal += $price * $qty;
                                    @endphp
                                    <li class="item uk-clearfix">
                                        <div class="colimg uk-float-left">
                                            <span class="img ec-scaledown"><img src="{{ \App\Support\LegacyFrontend::image($product->image ?? '') }}" alt="{{ data_get($product, 'pivot.name', '') }}"></span>
                                        </div>
                                        <div class="colinfo uk-float-right">
                                            <div class="row uk-flex uk-flex-space-between mb10">
                                                <div class="title ec-line-3">{{ data_get($product, 'pivot.name', '') }}</div>
                                                <div class="price">
                                                    <div class="tt-price"><strong>{{ number_format($price) }}đ</strong></div>
                                                    <div class="quantity">x{{ number_format($qty) }}</div>
                                                    <div class="tt-price"><strong>{{ number_format($price * $qty) }}đ</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="total">
                                <div class="row uk-flex uk-flex-middle uk-flex-space-between mb10">
                                    <div class="title">Tổng tiền</div>
                                    <div class="price"><strong>{{ number_format($orderTotal) }}đ</strong></div>
                                </div>
                                <div class="row mb10 uk-flex uk-flex-middle uk-flex-space-between">
                                    <div class="title">Giảm giá</div>
                                    <div class="price"><strong>{{ number_format($discountVoucher + $discountPromotion) }}đ</strong></div>
                                </div>
                                <div class="row mb10 uk-flex uk-flex-middle uk-flex-space-between">
                                    <div class="title">Phí vận chuyển</div>
                                    <div class="price"><strong>{{ number_format($order->shipping ?? 0) }}đ</strong></div>
                                </div>
                                <div class="row tt-price uk-flex uk-flex-middle uk-flex-space-between">
                                    <div class="title">Số tiền cần thanh toán</div>
                                    <div class="price"><strong>{{ number_format($orderTotal - $discountVoucher - $discountPromotion + ($order->shipping ?? 0)) }}đ</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
