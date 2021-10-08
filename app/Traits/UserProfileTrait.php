<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\DataResource;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait UserProfileTrait
{
    public function updateProfile(Request $request){
        $request->validate([
            "image"=> "nullable|image",
            "name"=> "required|string",
            "bio"=> "required|string"
        ]);
        $user = User::find($request->user()->id);
        if($request->has('image')){
            // Get File name and extension
            $FileNameWithExt = $request->file('image')->getClientOriginalName();
            // Get File name
            // $fileName = pathinfo($FileNameWithExt, PATHINFO_FILENAME);
            // Get File ext
            $fileExt = $request->file('image')->getClientOriginalExtension();
            // File name to store
            $fileNameToStore = time() . '.' . $fileExt;
            // Store Image
            $request->file('image')->storeAs('public/author_images', $fileNameToStore);
            list($width, $height) = getimagesize(storage_path('app/public/author_images/' . $fileNameToStore));
            //obtain ratio
            $imageratio = $width / $height;

            if ($imageratio >= 1) {
                $newwidth = 600;
                $newheight = 600 / $imageratio;
            } else {
                $newwidth = 400;
                $newheight = 400 / $imageratio;
            };
            Image::make(storage_path('app/public/author_images/' . $fileNameToStore))->resize($newwidth, $newheight)->save(storage_path('app/public/author_images/' . $fileNameToStore));

            if($user->image != null){
                Storage::delete('public/author_images/' . $user->image);
            }
            $user->image = $fileNameToStore;
        }
        if($request->has('name')){
            $user->name = $request->input('name');
        }
        if ($request->has('bio')) {
            $user->bio = $request->input('bio');
        }
        $user->save();
        return new DataResource($user);
    }
}