@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])
<form action="{{ route('redirect.destroy', $redirect->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Xóa chuyển hướng</div>
                    <div class="panel-description">
                        <p>Sau khi xóa, đường dẫn cũ sẽ không chuyển đi nữa (có thể thành trang 404).</p>
                        <p>Nếu chỉ muốn dừng tạm thời, hãy <a href="{{ route('redirect.edit', $redirect->id) }}">sửa</a> và chọn trạng thái "Tắt".</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <p><b>Đường dẫn cũ:</b> {{ $redirect->source_path }}</p>
                        <p><b>Chuyển tới:</b> {{ $redirect->target_url }} ({{ $redirect->status_code }})</p>
                        <p class="mb0"><b>Đã dùng:</b> {{ number_format($redirect->hits) }} lượt</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <a href="{{ route('redirect.index') }}" class="btn btn-default">Quay lại</a>
            <button class="btn btn-danger" type="submit">Xóa chuyển hướng</button>
        </div>
    </div>
</form>
