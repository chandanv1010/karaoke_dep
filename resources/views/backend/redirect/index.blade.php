@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])
<div class="row mt20">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="alert alert-info" style="margin-bottom:15px">
            Khai báo đường dẫn cũ → đường dẫn mới, có hiệu lực ngay sau khi lưu.
            Dữ liệu lưu trong CSDL nên cập nhật code mới không làm mất.
            Thay cho việc sửa file <code>.htaccess</code> (máy chủ dùng nginx, không đọc file này).
        </div>
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ $config['seo']['index']['table'] }} ({{ $redirects->total() }})</h5>
            </div>
            <div class="ibox-content">
                <form action="{{ route('redirect.index') }}" method="get" class="mb15">
                    <div class="uk-flex uk-flex-middle uk-flex-space-between">
                        <div class="input-group" style="max-width:420px">
                            <input type="text" name="keyword" value="{{ $keyword }}" class="form-control" placeholder="Tìm đường dẫn hoặc ghi chú...">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tìm</button>
                            </span>
                        </div>
                        <a href="{{ route('redirect.create') }}" class="btn btn-danger"><i class="fa fa-plus mr5"></i> Thêm chuyển hướng</a>
                    </div>
                </form>
                <table class="table table-striped table-bordered" style="table-layout:fixed;word-break:break-all">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Đường dẫn cũ</th>
                            <th>Chuyển tới</th>
                            <th style="width:70px" class="text-center">Kiểu</th>
                            <th style="width:110px" class="text-center">Trạng thái</th>
                            <th style="width:120px" class="text-center">Lượt / lần cuối</th>
                            <th style="width:110px" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($redirects as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>
                                    <a href="{{ url($r->source_path) }}" target="_blank" rel="noopener">{{ $r->source_path }}</a>
                                    @if($r->note)<div class="text-muted small" style="word-break:normal">{{ $r->note }}</div>@endif
                                </td>
                                <td>{{ $r->target_url }}</td>
                                <td class="text-center">{{ $r->status_code }}</td>
                                <td class="text-center">
                                    @if($r->publish == 2)
                                        <span class="label label-primary">Đang bật</span>
                                    @else
                                        <span class="label label-default">Tắt</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ number_format($r->hits) }}
                                    @if($r->last_hit_at)<div class="text-muted small">{{ $r->last_hit_at->format('d/m/Y') }}</div>@endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('redirect.edit', $r->id) }}" class="btn btn-success btn-sm"><i class="fa fa-edit"></i></a>
                                    <a href="{{ route('redirect.delete', $r->id) }}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Chưa có chuyển hướng nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $redirects->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
