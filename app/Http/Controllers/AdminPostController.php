<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class AdminPostController extends Controller
{
    public function addPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required',
            'file' => 'required',
            'file.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'post_cat_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $thumbnail = "";
        if ($request->has('file')) {
            $image = $request->file('file');
            $filename = time() . '_' . str_replace(' ', '', $image->getClientOriginalName());;
            $image->move('uploads/posts/', $filename);
            $thumbnail = 'uploads/posts/' . $filename;
        }
        $post = Post::create([
            'title' => $request->title,
            'slug' => $request->title,
            'content' => $request->content,
            'status' => $request->status,
            'thumbnail' => $thumbnail,
            'user_id' => Auth::id()
        ]);
        $arrListPostCat = PostCat::getAllParent((int)$request->post_cat_id);
        $post->postCategories()->attach($arrListPostCat);
        return response()->json([
            'status' => 200,
            'message' => 'Add Post Successfullly',
            'content' => []
        ], 200);
    }

    public function getAllPosts(Request $request)
    {
        $keyword = "";
        if ($request->keyword) {
            $keyword = $request->keyword;
        };
        if ($request->status == 'trash') {
            $posts = Post::onlyTrashed()->where('title', 'LIKE', "%$keyword%")->get();
        } else {
            $posts = Post::where('title', 'LIKE', "%$keyword%")->get();
        }
        foreach ($posts as $key => $post) {
            $post->user = $post->user;
            $post->postCategories;
        }
        $numberActivePosts = Post::all()->count();
        $numberTrashPosts = Post::onlyTrashed()->count();
        return response()->json([
            'status' => 200,
            'message' => 'Get All Post Successfully',
            'content' => [
                'posts' => $posts,
                'numberActivePosts' => $numberActivePosts,
                'numberTrashPosts' => $numberTrashPosts
            ]
        ], 200);
    }

    public function getDetailPost($id)
    {
        $post = Post::find($id);
        if ($post != null) {
            $post->postCategories;
            return response()->json([
                'status' => 200,
                'message' => 'Get Detail Post Successfully',
                'content' => $post
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Post found',
                'content' => []
            ], 400);
        }
    }

    public function editPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required',
            'post_cat_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $post = Post::find($request->id);
        if ($post != null) {
            $thumbnail = $post->thumbnail;
            if ($request->has('file')) {
                if (File::exists(public_path($post->thumbnail))) {
                    // //public_path in config/filesystems.php
                    File::delete(public_path($post->thumbnail));
                }
                $image = $request->file('file');
                $filename = time() . '_' . str_replace(' ', '', $image->getClientOriginalName());;
                $image->move('uploads/posts/', $filename);
                $thumbnail = 'uploads/posts/' . $filename;
            }
            $post->update([
                'title' => $request->title,
                'slug' => Post::createSlug($request->title),
                'content' => $request->content,
                'status' => $request->status,
                'thumbnail' => $thumbnail,
            ]);
            $arrListPostCat = PostCat::getAllParent((int)$request->post_cat_id);
            $post->postCategories()->sync($arrListPostCat);
            return response()->json([
                'status' => 200,
                'message' => 'Update Post Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Post found',
                'content' => []
            ], 400);
        }
    }

    public function softDeletePost($id)
    {
        $post = Post::find($id);
        if ($post != null) {
            $post->update([
                'status' => '0'
            ]);
            $post->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Soft Delete post successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No post found',
                'content' => []
            ], 400);
        }
    }

    public function forceDeletePost($id)
    {
        $post = Post::onlyTrashed()->find($id);
        if ($post != null) {
            if (File::exists(public_path($post->thumbnail))) {
                // //public_path in config/filesystems.php
                File::delete(public_path($post->thumbnail));
            }
            $post->forceDelete();
            return response()->json([
                'status' => 200,
                'message' => 'Permanently delete successful post',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No post found in trash',
                'content' => []
            ], 400);
        }
    }

    public function restorePost($id)
    {
        $post = Post::onlyTrashed()->find($id);
        if ($post != null) {
            $post->restore();
            return response()->json([
                'status' => 200,
                'message' => 'Post restore successful',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Post found in trash',
                'content' => []
            ], 400);
        }
    }
}
