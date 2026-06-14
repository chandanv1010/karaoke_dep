<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\QueryScopes;
use App\Support\SchemaCache;

class Product extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $fillable = [
        'image',
        'album',
        'publish',
        'follow',
        'order',
        'user_id',
        'product_catalogue_id',
        'price',
        'combo_price',
        'stock',
        'made_in',
        'code',
        'attributeCatalogue',
        'attribute',
        'variant',
        'qrcode',
        'warranty',
        'check',
        'seller_id',
        'total_lesson',
        'duration',
        'lecturer_id',
        'lession_content',
        'chapter',
        'iframe',
        'percent',
        'ml'
    ];

    protected $casts = [
        'attribute' => 'json',
        'chapter' => 'json'
    ];


    protected $table = 'products';

    public function languages()
    {
        $pivot = [
            'name',
            'canonical',
            'meta_title',
            'meta_keyword',
            'meta_description',
            'description',
            'content',
        ];

        if (SchemaCache::hasColumn('product_language', 'url')) {
            $pivot[] = 'url';
        }

        return $this->belongsToMany(Language::class, 'product_language', 'product_id', 'language_id')
            ->withPivot($pivot)->withTimestamps();
    }


    public function product_catalogues()
    {
        return $this->belongsToMany(ProductCatalogue::class, 'product_catalogue_product', 'product_id', 'product_catalogue_id')
            ->with('languages');
    }

    public function product_variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product_variant', 'product_id', 'promotion_id')
            ->withPivot(
                'variant_uuid',
                'model',
            )->withTimestamps();
    }


    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')
            ->withPivot(
                'uuid',
                'name',
                'qty',
                'price',
                'priceOriginal',
                'option',
            );
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function sellers()
    {
        return $this->belongsTo(Customer::class, 'seller_id', 'id');
    }

    public function lecturers()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'id');
    }
}
