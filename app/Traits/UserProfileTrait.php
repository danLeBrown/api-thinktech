<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\DataResource;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Api\Upload\UploadApi;
use PhpParser\Node\Stmt\TryCatch;

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
            try {
                $upload = (new UploadApi())->upload(storage_path('app/public/author_images/' . $fileNameToStore), [
                    "folder" => "think-tech/author_images/", 
                ]);
            } catch (\TypeError $e) {
                return $this->returnError([
                    "field"=> "image",
                    "code"=> 400,
                    "message"=> $e->getMessage()
                ]);
            }
        
            if($user->image_data != null){
                (new UploadApi())->destroy(json_decode($user->image_data, true)['public_id']);
                // $cloudinary->uploadApi()->destroy($public_id, $options = []);
                Storage::delete('public/author_images/' . $user->image);
            }
            $user->image_data = json_encode($upload);
        }
        if($request->has('name')){
            $user->name = $request->input('name');
        }
        if ($request->has('bio')) {
            $user->bio = $request->input('bio');
        }
        $user->save();
        $user->image_url = json_decode($user->image_data, true)['secure_url'];
        $user->relative_at = $this->timeago($user->created_at);
        return new DataResource([
            "user"=> $user,
            "message"=> "Profile has been updated succesfully!"
        ]);
    }
}