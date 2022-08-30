<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostCat extends Model
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

        static::created(function ($postCat) {
            $postCat->slug = $postCat->createSlug($postCat->name);
            $postCat->save();
        });
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


    public static function getDataTree($postCats, $parent_id = 0, $level = 0)
    {
        $result = [];
        foreach ($postCats as $key => $postCat) {
            if ($postCat->parent_id == $parent_id) {
                $postCat->level = $level;
                $result[] = $postCat;
                unset($postCats[$key]);
                $child = self::getDataTree($postCats, $postCat->id, $level + 1);
                $result = array_merge($result, $child);
            }
        }
        return $result;
    }

    //Hàm kiểm tra xem danh mục cha có hoạt động hay không
    public static function checkActiveParent($parent_id)
    {
        $postCat = PostCat::find($parent_id);
        if ($postCat->status == "1") {
            return true;
        }
        return false;
    }

    //Hàm kiểm tra xem tất cả danh mục cha bật hoạt động hết hay không
    public static function checkAllActiveParent($postCats, $parent_id)
    {
        foreach ($postCats as $key => $postCat) {
            if ($postCat->id == $parent_id) {
                if ($postCat->status == "0") {
                    return false;
                    break;
                }
                unset($postCats[$key]);
                self::checkAllActiveParent($postCats, $postCat->parent_id);
            }
        }
        return true;
    }

    //Hàm kiểm tra xem tất cả danh mục con đã tắt hoạt động hay không
    public static function checkAllInactiveChildren($postCats, $id)
    {
        foreach ($postCats as $key => $postCat) {
            if ($postCat->parent_id == $id) {
                if ($postCat->status == "1") {
                    return false;
                    break;
                }
                unset($postCats[$key]);
                self::checkAllInactiveChildren($postCats, $postCat->id);
            }
        }
        return true;
    }

    //Hàm kiểm tra xem có còn danh mục con hay không
    public static function checkHasChildren($id)
    {
        $postCats = PostCat::all();
        foreach ($postCats as $key => $postCat) {
            if ($postCat->parent_id == $id) {
                return true;
                break;
            }
        }
        return false;
    }

    //Hàm lấy tất cả danh mục cha 
    public static function getAllParent($parent_id)
    {
        $postCats = PostCat::all();
        $result = [];
        foreach ($postCats as $key => $postCat) {
            if ($postCat->id === $parent_id) {
                $result[] = $postCat->id;
                unset($postCats[$key]);
                $parent = self::getAllParent($postCat->parent_id);
                $result = array_merge($result, $parent);
            }
        }
        return $result;
    }
}
