<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'slug', 'price', 'description', 'detail', 'status_public',
    'status_feature', 'status_warehouse', 'user_id'];
    protected static function boot()
    {
        parent::boot();

        static::created(function ($product) {
            $product->slug = $product->createSlug($product->name);
            $product->save();
        });
    }

    public function productCategories(){
        return $this->belongsToMany(ProductCat::class);
    }

    public function productParentCategory(){
        return $this->belongsToMany(ProductCat::class)->where('parent_id', 0);
    }

    public function productThumbnails(){
        return $this->hasMany(ProductThumbnail::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public static function createSlug($name)
    {
        if (static::whereSlug($slug = Str::slug($name))->exists()) {
            $max = static::whereName($name)->latest('id')->skip(1)->value('slug');

            if (is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function ($mathces) {
                    return $mathces[1] + 1;
                }, $max);
            }

            return "{$slug}-2";
        }

        return $slug;
    }

    public static function checkExistImage($arrayString, $newString){
        foreach ($arrayString as $oldString) {
            if (strpos($newString, $oldString) !== FALSE) {
                return true;
            }
        }
        return false;
    }
}
