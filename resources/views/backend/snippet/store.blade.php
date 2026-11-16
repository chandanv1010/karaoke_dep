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
    $url = ($config['method'] == 'create') ? route('snippet.store') : route('snippet.update', $snippet->id);
    $position = old('position', $snippet->position ?? 'body_end');
    $selPages = old('pages', $snippet ? ($snippet->pageList() ?: array_keys(\App\Models\CodeSnippet::PAGES)) : array_keys(\App\Models\CodeSnippet::PAGES));
    $publish = (int) old('publish', $snippet->publish ?? 2);
@endphp
<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-4">
                <div class="panel-head">
                    <div class="panel-title">Mã nhúng</div>
                    <div class="panel-description">
                        <p>Dán nguyên đoạn mã nhà cung cấp đưa, ví dụ:<br>
                            <code>&lt;script src="https://widget...js" async&gt;&lt;/script&gt;</code></p>
                        <p><b>Widget chat, popup, nút nổi</b>: chọn <i>Cuối trang, trước &lt;/body&gt;</i>.</p>
                        <p><b>Mã cần hiện đúng chỗ</b> (khung đánh giá, form, banner): chọn vị trí tương ứng.
                           Nếu nhà cung cấp đưa cả thẻ <code>&lt;div id="..."&gt;</code> thì dán chung vào một ô.</p>
                        <p><b>Chỉ các URL</b>: mỗi dòng một đường dẫn, ví dụ <code>/lien-he.html</code>.
                           Thêm <code>*</code> ở cuối để áp dụng cho mọi trang bắt đầu như vậy, ví dụ <code>/loi-bai-hat*</code>. Để trống = không giới hạn.</p>
                        <p class="text-danger">Mã sai có thể làm hỏng giao diện. Nếu trang bị lỗi sau khi lưu, chọn "Tắt" là gỡ ngay.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-8">
                                <label class="control-label text-left">Tên <span class="text-danger">(*)</span></label>
                                <input type="text" name="name" value="{{ old('name', $snippet->name ?? '') }}" class="form-control" placeholder="Widget Jason chat" autocomplete="off">
                            </div>
                            <div class="col-lg-4">
                                <label class="control-label text-left">Trạng thái</label>
                                <select name="publish" class="form-control">
                                    <option value="2" @selected($publish === 2)>Đang bật</option>
                                    <option value="1" @selected($publish === 1)>Tắt</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <label class="control-label text-left">Mã nhúng <span class="text-danger">(*)</span></label>
                                <textarea name="code" rows="8" class="form-control" spellcheck="false"
                                    style="font-family:Consolas,monospace;font-size:13px">{{ old('code', $snippet->code ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-8">
                                <label class="control-label text-left">Vị trí <span class="text-danger">(*)</span></label>
                                <select name="position" class="form-control">
                                    @foreach(\App\Models\CodeSnippet::POSITIONS as $k => $label)
                                        <option value="{{ $k }}" @selected($position === $k)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label class="control-label text-left">Thứ tự (nhỏ trước)</label>
                                <input type="number" name="sort" min="0" value="{{ old('sort', $snippet->sort ?? 0) }}" class="form-control">
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <label class="control-label text-left">Hiện trên loại trang</label>
                                <div>
                                    @foreach(\App\Models\CodeSnippet::PAGES as $k => $label)
                                        <label style="font-weight:normal;margin-right:15px;white-space:nowrap">
                                            <input type="checkbox" name="pages[]" value="{{ $k }}" @checked(in_array($k, (array) $selPages, true))> {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <label class="control-label text-left">Chỉ các URL (không bắt buộc)</label>
                                <textarea name="urls" rows="3" class="form-control" placeholder="/lien-he.html&#10;/loi-bai-hat*">{{ old('urls', $snippet->urls ?? '') }}</textarea>
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label text-left">Ghi chú</label>
                                <textarea name="note" rows="3" class="form-control" placeholder="Của bên nào, hết hạn khi nào...">{{ old('note', $snippet->note ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <a href="{{ route('snippet.index') }}" class="btn btn-default">Quay lại</a>
            <button class="btn btn-primary" type="submit" name="send" value="send">Lưu lại</button>
        </div>
    </div>
</form>
