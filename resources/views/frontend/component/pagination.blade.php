@if ($model->hasPages())
<div class="pagination">
    @include('frontend.component.pagination-legacy', ['paginator' => $model])
</div>
@endif
