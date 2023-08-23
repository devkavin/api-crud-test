<?php

namespace App\Helpers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageHelper
{
    // public static function createImageUrl(Request $request, $model, $UrlPrefix = 'default')
    public static function createImageUrl(Request $request, $UrlPrefix = 'default')
    {
        // if image is not in the request, return null
        if (!$request->hasFile('image')) {
            return null;
        }
        // get the image file from the request
        $imageFile = $request->file('image');

        // ask dhanuka ayya about the file name format
        // for the time being, model name and current timestamp is used with time()
        $imageFilename = $UrlPrefix . '_' . time() . '.' . $imageFile->extension();
        // save the image to storage
        Storage::putFileAs('public/images/', $imageFile, $imageFilename);
        // $imageFile->move(public_path('images'), $imageFilename);
        $image_url = '/storage/images/' . $imageFilename;
        return $image_url;
    }

    public static function deleteImage($model)
    {
        // get the image url from the model
        $imageUrl = public_path() . $model->image_url;

        // delete the image from public path in root
        // unlink($imageUrl); // this is working
        Storage::delete($imageUrl); // this is not working

        // delete the image url from the model
        $model->image_url = null;
        $model->save();

        return $model;
    }
}
