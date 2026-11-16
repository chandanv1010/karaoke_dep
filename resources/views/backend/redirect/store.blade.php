@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method']]['title']])
@if ($errors->any())
    <div class="alert alert-danger mt20">
        <ul style="margin:0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@php
    $url = ($config['method'] == 'create') ? route('redirect.store') : route('redirect.update', $redirect->id);
    $code = (int) old('status_code', $redirect->status_code ?? 301);
    $publish = (int) old('publish', $redirect->publish ?? 2);
@endphp
<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chuyển hướng</div>
                    <div class="panel-description">
                        <p>Khách (và Google) mở <b>đường dẫn cũ</b> sẽ được đưa sang <b>đường dẫn mới</b>.</p>
                        <p>Có thể dán nguyên link đầy đủ, hệ thống tự cắt về dạng <code>/ten-trang.html</code>.</p>
                        <p><b>301</b>: chuyển vĩnh viễn (dùng khi đổi URL, gộp bài) - Google chuyển điểm SEO sang trang mới.<br>
                           <b>302</b>: chuyển tạm thời (khuyến mãi, bảo trì).</p>
                        <p>Lưu ý: Những trường đánh dấu <span class="text-danger">(*)</span> là bắt buộc</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Đường dẫn cũ <span class="text-danger">(*)</span></label>
                                    <input type="text" name="source_path" value="{{ old('source_path', $redirect->source_path ?? '') }}"
                                        class="form-control" placeholder="/kien-thuc-phong-thuy.html" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Đường dẫn mới <span class="text-danger">(*)</span></label>
                                    <input type="text" name="target_url" value="{{ old('target_url', $redirect->target_url ?? '') }}"
                                        class="form-control" placeholder="/phong-thuy.html hoặc https://trang-khac.com/..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Kiểu chuyển hướng</label>
                                    <select name="status_code" class="form-control">
                                        <option value="301" @selected($code === 301)>301 - Vĩnh viễn</option>
                                        <option value="302" @selected($code === 302)>302 - Tạm thời</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Trạng thái</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" @selected($publish === 2)>Đang bật</option>
                                        <option value="1" @selected($publish === 1)>Tắt</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Ghi chú</label>
                                    <input type="text" name="note" value="{{ old('note', $redirect->note ?? '') }}"
                                        class="form-control" placeholder="Vì sao chuyển (không bắt buộc)" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <a href="{{ route('redirect.index') }}" class="btn btn-default">Quay lại</a>
            <button class="btn btn-primary" type="submit" name="send" value="send">Lưu lại</button>
        </div>
    </div>
</form>
