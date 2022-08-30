<?php

namespace App\Http\Controllers;

use App\Models\ProductCat;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AdminProductCatController extends Controller
{
    public function getAllListProductCats(Request $request)
    {
        $keyword = "";
        if ($request->keyword) {
            $keyword = $request->keyword;
        };
        $listProductCats = ProductCat::where('name', 'LIKE', "%$keyword%")->get();
        $listProductCatsDataTree = ProductCat::getDataTree($listProductCats);
        foreach ($listProductCatsDataTree as $key => $productCat) {
            $listProductCatsDataTree[$key]->user = $productCat->user;
        }
        return response()->json([
            'status' => 200,
            'message' => 'get all product categories successfully',
            'content' => $listProductCatsDataTree
        ], 200);
    }

    public function getAllListActiveProductCats(){
        $productCats = ProductCat::where('status', "1")->get();
        $listProductCatsDataTree = ProductCat::getDataTree($productCats);
        return response()->json([
            'status'=> 200,
            'message'=> 'Get All Active Product Cat Successfully',
            'content'=> $listProductCatsDataTree
        ],200);
    }

    public function addProductCat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|',
            'parent_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ]);
        }
        $status = $request->status;
        if ($request->parent_id != 0) {
            if (!ProductCat::checkActiveParent($request->parent_id)) {
                $status = "0";
            }
        }
        $productCat = ProductCat::create([
            'name' => $request->name,
            'slug' => $request->name,
            'status' => $status,
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Create Product Category Successfully',
        ], 200);
    }

    public function getDetailProductCatById($id)
    {
        $productCat = ProductCat::find($id);
        if ($productCat != null) {
            return response()->json([
                'status' => 200,
                'message' => 'Get Detail Product Category Successfully',
                'content' => $productCat
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Product Category Found',
                'content' => []
            ], 400);
        }
    }

    public function editProductCat(Request $request)
    {
        $productCat = ProductCat::find($request->id);
        if ($productCat != null) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'status' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Validation Fails',
                    'content' => $validator->errors()
                ]);
            }
            $listProductCats = ProductCat::all();
            if ($request->status == "1") {
                if (ProductCat::checkAllActiveParent($listProductCats, $productCat->parent_id)) {
                    if ($request->name == $productCat->name) {
                        $productCat->update([
                            'status' => $request->status
                        ]);
                    } else {
                        $productCat->update([
                            'name' => $request->name,
                            'slug' => ProductCat::createSlug($request->name),
                            'status' => $request->status
                        ]);
                    }
                    return response()->json([
                        'status' => 200,
                        'message' => 'Update Product Category Successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'You must enable parent category activity',
                    ], 400);
                }
            } else {
                if (ProductCat::checkAllInactiveChildren($listProductCats, $productCat->id)) {
                    if ($request->name == $productCat->name) {
                        $productCat->update([
                            'status' => $request->status
                        ]);
                    } else {
                        $productCat->update([
                            'name' => $request->name,
                            'slug' => ProductCat::createSlug($request->name),
                            'status' => $request->status
                        ]);
                    }
                    return response()->json([
                        'status' => 200,
                        'message' => 'Update Product Category Successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'You must disable subcategory activity',
                    ], 400);
                }
            }
        }
    }

    public function deleteProductCat($id)
    {
        $productCat = ProductCat::find($id);
        if ($productCat != null) {
            if (ProductCat::checkHasChildren($id)) {
                return response()->json([
                    'status' => '400',
                    'message' => 'You must delete all subcategories'
                ], 400);
            } else {
                $productCat->delete();
                return response()->json([
                    'status' => '200',
                    'message' => 'Delete category successfully'
                ], 200);
            }
        }else{
            return response()->json([
                'status' => 400,
                'message' => 'No Product Category Found',
                'content' => []
            ], 400);
        }
    }

}
