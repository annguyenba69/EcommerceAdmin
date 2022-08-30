<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class AdminSlideController extends Controller
{
    public function addSlide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
            'file.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required'
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
            $image->move('uploads/slides/', $filename);
            $thumbnail = 'uploads/slides/' . $filename;
        }
        Slide::create([
            'thumbnail' => $thumbnail,
            'status' => $request->status,
            'user_id' => Auth::id()
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Add Slide Successfullly',
            'content' => []
        ], 200);
    }

    public function getAllSlides(Request $request)
    {
        if ($request->status == "trash") {
            $listSlides = Slide::onlyTrashed()->get();
        }else{
            $listSlides = Slide::all();
        }
        foreach ($listSlides as $key => $slide) {
            $slide->user;
        };
        $numSlide = Slide::all()->count();
        $numTrashedSlide = Slide::onlyTrashed()->count();
        return response()->json([
            'status' => 200,
            'message' => 'Get All Slide Successfully',
            'content' => [
                'listSlides' => $listSlides,
                'numSlide' => $numSlide,
                'numTrashedSlide' => $numTrashedSlide
            ]
        ], 200);
    }

    public function changeStatusSlide(Request $request, $id)
    {
        $slide = Slide::find($id);
        if ($slide != null) {
            $status = "0";
            if($slide->status == "0"){
                $status = "1";
            }
            $slide->update([
                'status' => $status
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Update Slide Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Slide Found',
                'content' => []
            ], 400);
        }
    }

    public function softDeleteSlide($id)
    {
        $slide = Slide::find($id);
        if ($slide != null) {
            $slide->update([
                'status' => '0'
            ]);
            $slide->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Soft Delete Slide Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Slide Found',
                'content' => []
            ], 400);
        }
    }

    public function forceDeleteSlide($id)
    {
        $slide = Slide::onlyTrashed()->find($id);
        if ($slide != null) {
            if (File::exists(public_path($slide->thumbnail))) {
                // //public_path in config/filesystems.php
                File::delete(public_path($slide->thumbnail));
            }
            $slide->forceDelete();
            return response()->json([
                'status' => 200,
                'message' => 'Force Delete Slide Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Slide Found',
                'content' => []
            ], 400);
        }
    }

    public function restoreSlide($id)
    {
        $slide = Slide::onlyTrashed()->find($id);
        if ($slide != null) {
            $slide->restore();
            return response()->json([
                'status' => 200,
                'message' => 'Restore Slide Successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No Slide Found',
                'content' => []
            ], 400);
        }
    }
}
