<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCat;
use App\Models\ProductThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminProductController extends Controller
{
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'detail' => 'required|string',
            'product_cat_id' => 'required',
            'files' => 'required',
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_public' => 'required',
            'status_feature' => 'required',
            'status_warehouse' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $product = Product::create([
            'name' => $request->name,
            'slug' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'detail' => $request->detail,
            'status_public' => $request->status_public,
            'status_feature' => $request->status_feature,
            'status_warehouse' => $request->status_warehouse,
            'user_id' => Auth::id()
        ]);
        $arrListProductCat = ProductCat::getAllParent((int)$request->product_cat_id);
        $product->productCategories()->attach($arrListProductCat);
        if ($request->has('files')) {
            foreach ($request->file('files') as $image) {
                $filename = time() . '_' . str_replace(' ', '', $image->getClientOriginalName());;

                $image->move('uploads/products/', $filename);
                $dir = 'uploads/products/' . $filename;
                $productThumbnail = new ProductThumbnail(['thumbnail' => $dir]);
                $product->productThumbnails()->save($productThumbnail);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => 'Add Product Successfullly',
            'content' => $request->file('files')
        ], 200);
    }

    public function getAllProducts(Request $request)
    {
        $keyword = "";
        if ($request->keyword) {
            $keyword = $request->keyword;
        };
        if ($request->status == 'trash') {
            $listProducts = Product::onlyTrashed()->where('name', 'LIKE', "%$keyword%")->get();
        } else {
            $listProducts = Product::where('name', 'LIKE', "%$keyword%")->get();
        }
        foreach ($listProducts as $key => $product) {
            $product->user = $product->user;
            $product->productThumbnails;
            $product->productCategories;
        }
        $numberActiveProducts = Product::all()->count();
        $numberTrashProducts = Product::onlyTrashed()->count();
        return response()->json([
            'status' => 200,
            'message' => 'Get All Product Successfully',
            'content' => [
                'listProducts'=> $listProducts,
                'numberActiveProducts'=> $numberActiveProducts,
                'numberTrashProducts'=> $numberTrashProducts
            ]
        ], 200);
    }

    public function getDetailProduct($id)
    {
        $product = Product::find($id);
        if ($product != null) {
            $product->productThumbnails;
            $product->productParentCategory;
            return response()->json([
                'status' => 200,
                'message' => 'Get Detail Product Successfully',
                'content' => $product
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Products found',
                'content' => []
            ], 400);
        }
    }

    public function editProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'detail' => 'required|string',
            'product_cat_id' => 'required',
            'status_public' => 'required',
            'status_feature' => 'required',
            'status_warehouse' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        };
        $product = Product::find($request->id);
        $product->update([
            'name' => $request->name,
            'slug' => Product::createSlug($request->name),
            'price' => $request->price,
            'description' => $request->description,
            'detail' => $request->detail,
            'status_public' => $request->status_public,
            'status_feature' => $request->status_feature,
            'status_warehouse' => $request->status_warehouse,
        ]);

        if ($request->has('files') && $request->file('files') != null) {
            foreach($product->productThumbnails as $thumbnail){
                if (File::exists(public_path($thumbnail->thumbnail))) {
                    // //public_path in config/filesystems.php
                    File::delete(public_path($thumbnail->thumbnail));
                }
            }
            $product->productThumbnails()->delete();
            foreach ($request->file('files') as $image) {
                $filename = time() . '_' . str_replace(' ', '', $image->getClientOriginalName());
                $image->move('uploads/products/', $filename);
                $dir = 'uploads/products/' . $filename;
                $productThumbnail = new ProductThumbnail(['thumbnail' => $dir]);
                $product->productThumbnails()->save($productThumbnail);
            }
        }
        $arrListProductCat = ProductCat::getAllParent((int)$request->product_cat_id);
        $product->productCategories()->sync($arrListProductCat);
        return response()->json([
            'status' => 200,
            'message' => 'Update Product Successfully',
            'content' => []
        ], 200);
    }

    public function softDeleteProduct($id)
    {
        $product = Product::find($id);
        if ($product != null) {
            $product->update([
                'status_public'=> '0'
            ]);
            $product->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Soft Delete product successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No products found',
                'content' => []
            ], 400);
        }
    }

    public function forceDeleteProduct($id)
    {
        $product = Product::onlyTrashed()->find($id);
        if ($product != null) {
            foreach ($product->productThumbnails as $key => $thumbnail) {
                if (File::exists(public_path($thumbnail->thumbnail))) {
                    //public_path in config/filesystems.php
                    File::delete(public_path($thumbnail->thumbnail));
                }
            }
            $product->forceDelete();
            return response()->json([
                'status' => 200,
                'message' => 'Permanently delete successful product',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No products found in trash',
                'content' => []
            ], 400);
        }
    }

    public function restoreProduct($id)
    {
        $product = Product::onlyTrashed()->find($id);
        if ($product != null) {
            $product->restore();
            return response()->json([
                'status' => 200,
                'message' => 'Product restore successful',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Products found in trash',
                'content' => []
            ], 400);
        }
    }

    public function getAllFile()
    {
        $images = ProductThumbnail::all();
        return response()->json(["status" => "success", "count" => count($images), "data" => $images]);
    }
}
