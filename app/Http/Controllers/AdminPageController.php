<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminPageController extends Controller
{
    public function addPage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $page = Page::create([
            'name' => $request->name,
            'slug' => $request->name,
            'content' => $request->content,
            'status' => $request->status,
            'user_id' => Auth::id()
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Add Page Successfullly',
            'content' => []
        ], 200);
    }

    public function getDetailPage($id)
    {
        $page = Page::find($id);
        if ($page != null) {
            $page->user;
            return response()->json([
                'status' => 200,
                'message' => 'Get Detail Page Successfully',
                'content' => $page
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Page Found',
                'content' => []
            ], 400);
        }
    }

    public function editPage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' => 'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $page = Page::find($request->id);
        if ($page != null) {
            $page->update([
                'name' => $request->name,
                'slug' => Page::createSlug($request->name),
                'content' => $request->content,
                'status' => $request->status,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Update Page Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Page Found',
                'content' => []
            ], 400);
        }
    }

    public function getAllListPages(Request $request)
    {
        $keyword = "";
        if ($request->keyword) {
            $keyword = $request->keyword;
        }
        if ($request->status == "trash") {
            $pages = Page::onlyTrashed()->where('name', 'LIKE', "%$keyword%")->get();
        } else {
            $pages = Page::where('name', 'LIKE', "%$keyword%")->get();
        }
        foreach ($pages as $key => $page) {
            $page->user;
        };
        $numPages = Page::all()->count();
        $numTrashPages = Page::onlyTrashed()->count();
        return response()->json([
            'status' => 200,
            'message' => 'Get List Pages Successfully',
            'content' => [
                'pages' => $pages,
                'numPages' => $numPages,
                'numTrashPages' => $numTrashPages
            ]
        ], 200);
    }

    public function softDeletePage($id)
    {
        $page = Page::find($id);
        if ($page != null) {
            $page->update([
                'status' => '0'
            ]);
            $page->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Soft Delete Page Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Page Found',
                'content' => []
            ], 400);
        }
    }

    public function restorePage($id)
    {
        $page = Page::onlyTrashed()->find($id);
        if ($page != null) {
            $page->restore();
            return response()->json([
                'status' => 200,
                'message' => 'Restore Page Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Page Found',
                'content' => []
            ], 400);
        }
    }

    public function forceDeletePage($id){
        $page = Page::onlyTrashed()->find($id);
        if ($page != null) {
            $page->forceDelete();
            return response()->json([
                'status' => 200,
                'message' => 'Force Delete Page Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Page Found',
                'content' => []
            ], 400);
        }
    }
}
