<?php

namespace App\Http\Controllers;

use App\Models\PostCat;
use App\Models\ProductCat;
use App\PostCat as AppPostCat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminPostCatController extends Controller
{
    public function getAllListPostCat(Request $request){
        $keyword = "";
        if($request->keyword){
           $keyword = $request->keyword;
        };
        $postCats = PostCat::where('name', 'LIKE', "%$keyword%")->get();
        $postCatsDataTree = ProductCat::getDataTree($postCats);
        foreach ($postCatsDataTree as $key => $postCat){
            $postCatsDataTree[$key] -> user = $postCat->user;
        }
        return response()->json([
            'status' => 200,
            'message' => 'Get all post categories successfully',
            'content' => $postCatsDataTree
        ],200);
    }

    public function getAllListActivePostCat(){
        $activePostCats = PostCat::where('status', '1')->get();
        $activePostCatsDataTree = PostCat::getDataTree($activePostCats);
        return response()->json([
            'status'=> 200,
            'message'=> 'Get all active post categories successfully',
            'content'=>$activePostCatsDataTree
        ],200);
    }

    public function addPostCat(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'status'=> 'required',
            'parent_id'=> 'required|integer'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ]);
        }
        $status = $request->status;
        if($request->parent_id != 0){
            if(!PostCat::checkActiveParent($request->parent_id)){
                $status = "0";
            }
        }

        $postCat = PostCat::create([
            'name'=> $request->name,
            'slug'=> $request->name,
            'status'=> $status,
            'parent_id'=> $request->parent_id,
            'user_id'=> Auth::id()
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Create Product Category Successfully',
            'content'=>[]
        ], 200);
    }

    public function getDetailPostCatById($id){
        $postCat = PostCat::find($id);
        if($postCat != null){
            return response()->json([
                'status' => 200,
                'message' => 'Get Detail Post Category Successfully',
                'content' => $postCat
            ], 200);
        }else{
            return response()->json([
                'status' => 200,
                'message' => 'No Post Category Found',
                'content' => []
            ], 200);
        }
    }

    public function editPostCat(Request $request){
        $postCat = PostCat::find($request->id);
        if($postCat != null){
            $validator = Validator::make($request->all(),[
                'name'=> 'required|string|max:255',
                'status'=> 'required'
            ]);
            if($validator->fails()){
                return response()->json([
                    'status' => 401,
                    'message' => 'Validation Fails',
                    'content' => $validator->errors()
                ]);
            };
            $listPostCats = PostCat::all();
            if($request->status == "1"){
                if(PostCat::checkAllActiveParent($listPostCats, $postCat->parent_id)){
                    if($request->name == $postCat->name){
                        $postCat->update([
                            'status'=> $request->status
                        ]);
                    }else{
                        $postCat->update([
                            'name'=> $request->name,
                            'slug'=> PostCat::createSlug($request->name),
                            'status'=> $request->status
                        ]);
                    }
                    return response()->json([
                        'status' => 200,
                        'message' => 'Update Product Category Successfully',
                    ], 200);
                }else{
                    return response()->json([
                        'status' => 400,
                        'message' => 'You must enable parent category activity',
                    ], 400);
                }
            }else{
                if(PostCat::checkAllInactiveChildren($listPostCats, $postCat->id)){
                    if($request->name == $postCat->name){
                        $postCat->update([
                            'status'=> $request->status
                        ]);
                    }else{
                        $postCat->update([
                            'name'=> $request->name,
                            'slug'=> PostCat::createSlug($request->name),
                            'status'=> $request->status
                        ]);
                    }
                    return response()->json([
                        'status' => 200,
                        'message' => 'Update Product Category Successfully',
                    ], 200);
                }else{
                    return response()->json([
                        'status' => 400,
                        'message' => 'You must disable subcategory activity',
                    ], 400);
                }
            }
        }else{
            return response()->json([
                'status' => 200,
                'message' => 'No Post Category Found',
                'content' => []
            ], 200);
        }
    }

    public function deletePostCat($id){
        $postCat = PostCat::find($id);
        if($postCat != null){
            if(PostCat::checkHasChildren($id)){
                return response()->json([
                    'status' => '400',
                    'message' => 'You must delete all subcategories'
                ], 400);
            }else{
                $postCat->delete();
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
