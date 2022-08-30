<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCat extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'status', 'parent_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::created(function ($productCat) {
            $productCat->slug = $productCat->createSlug($productCat->name);
            $productCat->save();
        });
        // static::updated(function ($productCat) {
        //     $productCat->slug = $productCat->createSlug($productCat->name);
        //     $productCat->save();
        // });
    }

    /** 
     * Write code on Method
     *
     * @return response()
     */
    
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


    public static function getDataTree($productCats, $parent_id = 0, $level = 0)
    {
        $result = [];
        foreach ($productCats as $key => $productCat) {
            if ($productCat->parent_id == $parent_id) {
                $productCat->level = $level;
                $result[] = $productCat;
                unset($productCats[$key]);
                $child = self::getDataTree($productCats, $productCat->id, $level + 1);
                $result = array_merge($result, $child);
            }
        }
        return $result;
    }

    //Hàm kiểm tra xem danh mục cha có hoạt động hay không
    public static function checkActiveParent($parent_id)
    {
        $productCat = ProductCat::find($parent_id);
        if ($productCat->status == "1") {
            return true;
        }
        return false;
    }

    //Hàm kiểm tra xem tất cả danh mục cha bật hoạt động hết hay không
    public static function checkAllActiveParent($listProductCats, $parent_id)
    {
        foreach ($listProductCats as $key => $productCat) {
            if ($productCat->id == $parent_id) {
                if ($productCat->status == "0") {
                    return false;
                    break;
                }
                unset($listProductCats[$key]);
                self::checkAllActiveParent($listProductCats, $productCat->parent_id);
            }
        }
        return true;
    }

    //Hàm kiểm tra xem tất cả danh mục con đã tắt hoạt động hay không
    public static function checkAllInactiveChildren($listProductCats, $id)
    {
        foreach ($listProductCats as $key => $productCat) {
            if ($productCat->parent_id == $id) {
                if ($productCat->status == "1") {
                    return false;
                    break;
                }
                unset($listProductCats[$key]);
                self::checkAllInactiveChildren($listProductCats, $productCat->id);
            }
        }
        return true;
    }

    //Hàm kiểm tra xem có còn danh mục con hay không
    public static function checkHasChildren($id){
        $listProductCats = ProductCat::all();
        foreach($listProductCats as $key => $productCat){
            if($productCat->parent_id == $id){
                return true;
                break;
            }
        }
        return false;
    }

    //Hàm lấy tất cả danh mục cha 
    public static function getAllParent($parent_id){
        $listProductCats = ProductCat::all();
        $result = [];
        foreach($listProductCats as $key => $productCat){
            if($productCat->id === $parent_id){
                $result[] = $productCat->id;
                unset($listProductCats[$key]);
                $parent = self::getAllParent($productCat->parent_id);
                $result = array_merge($result, $parent);
            }
        }
        return $result;
    }
}
