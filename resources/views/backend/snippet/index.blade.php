@php
    $positions = \App\Models\CodeSnippet::POSITIONS;
    $pagesAll = \App\Models\CodeSnippet::PAGES;
@endphp
@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])
<div class="row mt20">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="alert alert-info" style="margin-bottom:15px">
            Dán mã nhúng (widget chat, pixel, form, khung đánh giá...) và chọn vị trí hiển thị. Có hiệu lực ngay sau khi lưu,
            cập nhật code mới không làm mất. Mã Google Analytics/Ads dùng chung cho mọi trang vẫn nằm ở
            <a href="{{ route('system.index') }}">Cấu hình chung → Cấu hình script</a>.
        </div>
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ $config['seo']['index']['table'] }} ({{ $snippets->total() }})</h5>
            </div>
            <div class="ibox-content">
                <form action="{{ route('snippet.index') }}" method="get" class="mb15">
                    <div class="uk-flex uk-flex-middle uk-flex-space-between">
                        <div class="input-group" style="max-width:420px">
                            <select name="position" class="form-control">
                                <option value="">-- Tất cả vị trí --</option>
                                @foreach($positions as $k => $label)
                                    <option value="{{ $k }}" @selected(request('position') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Lọc</button>
                            </span>
                        </div>
                        <a href="{{ route('snippet.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i> Thêm mã nhúng</a>
                    </div>
                </form>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Tên</th>
                            <th>Vị trí</th>
                            <th>Áp dụng</th>
                            <th style="width:100px" class="text-center">Trạng thái</th>
                            <th style="width:110px" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($snippets as $s)
                            <tr>
                                <td>{{ $s->id }}</td>
                                <td>
                                    <b>{{ $s->name }}</b>
                                    @if($s->note)<div class="text-muted small">{{ $s->note }}</div>@endif
                                </td>
                                <td>{{ $positions[$s->position] ?? $s->position }}</td>
                                <td class="small">
                                    @if($s->pageList())
                                        {{ collect($s->pageList())->map(fn ($p) => $pagesAll[$p] ?? $p)->implode(', ') }}
                                    @else
                                        Mọi loại trang
                                    @endif
                                    @if($s->urlList())
                                        <div class="text-muted">Chỉ URL: {{ implode(', ', $s->urlList()) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($s->publish == 2)
                                        <span class="label label-primary">Đang bật</span>
                                    @else
                                        <span class="label label-default">Tắt</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('snippet.edit', $s->id) }}" class="btn btn-success btn-sm"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('snippet.delete', $s->id) }}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Chưa có mã nhúng nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $snippets->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
