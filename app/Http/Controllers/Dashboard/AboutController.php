<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AboutBanner;
use App\Models\AboutParalax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AboutController extends Controller
{
    public function createBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_path = $image->store('aboutbanner', 'public');
        }
        $banner = new  AboutParalax();
        $banner->title = $request->title;
        $banner->content = $request->content;
        $banner->image = $image_path;
        if ($banner->save()) {
            return response()->json([
                "data" => $banner,
                "message" => "About banner created successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "About banner not created",
                "success" => false
            ], 500);
        }
    }

    public function getBanners()
    {
        $banners = AboutBanner::all();
        return response()->json([
            "data" => $banners
        ], 200);
    }

    public function createParalax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_path = $image->store('aboutparalax', 'public');
        }
        $paralax = new  AboutParalax();
        $paralax->title = $request->title;
        $paralax->content = $request->content;
        $paralax->image = $image_path;
        if ($paralax->save()) {
            return response()->json([
                "data" => $paralax,
                "message" => "About paralax created successfully",
                "success" => true
            ], 200);
        } else {
            return response()->json([
                "message" => "About paralax not created",
                "success" => false
            ], 500);
        }
    }
}
