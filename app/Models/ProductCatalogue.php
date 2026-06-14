<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\QueryScopes;
use App\Support\SchemaCache;

class ProductCatalogue extends Model
{
    use HasFactory, SoftDeletes, QueryScopes;

    protected $fillable = [
        'parent_id',
        'lft',
        'rgt',
        'level',
        'image',
        'icon',
        'album',
        'publish',
        'follow',
        'order',
        'user_id',
        'attribute',
        'check',
        'short_name'
    ];

    protected $table = 'product_catalogues';

    protected $attributes = [
        'publish' => 2,
    ];
    
    public function languages(){
        $pivot = [
            'product_catalogue_id',
            'language_id',
            'name',
            'canonical',
            'meta_title',
            'meta_keyword',
            'meta_description',
            'description',
            'content',
        ];

        if (SchemaCache::hasColumn('product_catalogue_language', 'url')) {
            $pivot[] = 'url';
        }

        return $this->belongsToMany(Language::class, 'product_catalogue_language' , 'product_catalogue_id', 'language_id')
        ->withPivot($pivot)->withTimestamps();
    }

    protected $casts = [
        'attribute' => 'json',
    ];

    public function products(){
        return $this->belongsToMany(Product::class, 'product_catalogue_product', 'product_catalogue_id', 'product_id')
            ->orderBy('order', 'desc');
    }


    public function product_catalogue_language(){
        return $this->hasMany(ProductCatalogueLanguage::class, 'product_catalogue_id', 'id')->where('language_id','=',1);
    }

    public static function isNodeCheck($id = 0){
        $productCatalogue = ProductCatalogue::find($id);

        if($productCatalogue->rgt - $productCatalogue->lft !== 1){
            return false;
        } 

        return true;
        
    }

    public function getNameByLanguage($id, $language){
        
    }


}
