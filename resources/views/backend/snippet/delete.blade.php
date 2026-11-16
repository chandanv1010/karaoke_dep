@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])
<form action="{{ route('snippet.destroy', $snippet->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Xóa mã nhúng</div>
                    <div class="panel-description">
                        <p>Mã sẽ bị gỡ khỏi website và không khôi phục được.</p>
                        <p>Nếu chỉ muốn dừng tạm, hãy <a href="{{ route('snippet.edit', $snippet->id) }}">sửa</a> và chọn "Tắt".</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <p><b>Tên:</b> {{ $snippet->name }}</p>
                        <p><b>Vị trí:</b> {{ \App\Models\CodeSnippet::POSITIONS[$snippet->position] ?? $snippet->position }}</p>
                        <pre style="max-height:200px;overflow:auto;white-space:pre-wrap">{{ $snippet->code }}</pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <a href="{{ route('snippet.index') }}" class="btn btn-default">Quay lại</a>
            <button class="btn btn-danger" type="submit">Xóa mã nhúng</button>
        </div>
    </div>
</form>
