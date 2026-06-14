<div class="ibox w">
    <div class="ibox-title">
        <h5>{{ __('messages.parent') }}</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <span class="text-danger notice" >*{{ __('messages.parentNotice') }}</span>
                    <select name="parent_id" class="form-control setupSelect2" id="">
                        @foreach($dropdown as $key => $val)
                        <option {{ 
                            $key == old('parent_id', (isset($productCatalogue->parent_id)) ? $productCatalogue->parent_id : '') ? 'selected' : '' 
                            }} value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ibox w">
    <div class="ibox-title">
        <h5>{{ __('messages.image') }}</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <span class="image img-cover image-target"><img src="{{ (old('image', ($productCatalogue->image) ?? '' ) ? old('image', ($productCatalogue->image) ?? '')   :  'backend/img/not-found.jpg') }}" alt=""></span>
                    <input type="hidden" name="image" value="{{ old('image', ($productCatalogue->image) ?? '' ) }}">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ibox w">
    <div class="ibox-title">
        <h5>Icon Danh mục</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <input 
                        type="text" 
                        name="icon" 
                        value="{{ old('icon', ($productCatalogue->icon) ?? '' ) }}"
                        class="upload-image form-control"
                    >
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.dashboard.component.publish', ['model' => ($productCatalogue) ?? null, 'hideImage' => true])
