<?php

namespace App\Helpers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageHelper
{
    // public static function createImageUrl(Request $request, $UrlPrefix = 'default')
    // {
    //     // if image is not in the request, return null
    //     if (!$request->hasFile('image')) {
    //         return null;
    //     }
    //     // get the image file from the request
    //     $imageFile = $request->file('image');

    //     // ask dhanuka ayya about the file name format
    //     // for the time being, model name and current timestamp is used with time()
    //     $imageFilename = $UrlPrefix . '_' . time() . '.' . $imageFile->extension();
    //     // check if the image is already in the storage, if so, delete it and save the new one
    //     // if ($model->image_url) {
    //     //     self::deleteImage($model);
    //     // }
    //     // save the image
    //     // $imageFile->move(public_path('images'), $imageFilename);
    //     // save the image url to the model
    //     // SYMLINK CREATED using php artisan storage:link in public folder to storage folder
    //     // because by default, laravel does not allow to access files in storage folder
    //     $image_url = 'public/images/' . $imageFilename;
    //     // LAST EDIT HERE
    //     // PATH IN POSTMAN THROUGH CLICK NOT WORKING
    //     // store in storage/api/images
    //     Storage::putFile('images/', $imageFile, $imageFilename);
    //     return $image_url;
    // }

    // public static function createImageUrl(Request $request, $UrlPrefix = 'default')
    // {
    //     // if image is not in the request, return null
    //     if (!$request->hasFile('image')) {
    //         return null;
    //     }
    //     // get the image file from the request
    //     $imageFile = $request->file('image');

    //     // ask dhanuka ayya about the file name format
    //     // for the time being, model name and current timestamp is used with time()
    //     $imageFilename = $UrlPrefix . '_' . time() . '.' . $imageFile->extension();
    //     // save the image
    //     $imageFile->move(public_path('images'), $imageFilename);
    //     $image_url = '/images/' . $imageFilename;

    //     return $image_url;
    // }
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
        Storage::putFileAs('images/', $imageFile, $imageFilename);
        // $imageFile->move(public_path('images'), $imageFilename);
        $image_url = '/images/' . $imageFilename;


        return $image_url;
    }

    public static function saveImage($model, $image_url)
    {
        // save the image url to the model
        $model->image_url = $image_url;
        $model->save();

        return $model;
    }

    public static function deleteImage($model)
    {
        // get the image url from the model
        $imageUrl = public_path() . $model->image_url;

        // delete the image from public path in root
        unlink($imageUrl); // this is working
        // Storage::delete($imageUrl); // this is not working

        // delete the image url from the model
        $model->image_url = null;
        $model->save();

        return $model;
    }

    // public static function validateRequest($schema, $request, $type = 'insert')
    // {
    //     // Get schema keys into a array (for validation)
    //     $schema_keys = array_keys($schema);
    //     $input = $request;

    //     // Only get full request object when creating a new record (insert) and remove unnecessary fields (only)
    //     // image is a file, so we need to check it against the schema

    //     if ($type == 'insert') {
    //         $input = $request->only($schema_keys);
    //     }

    //     $validator = Validator::make($input, $schema);
    //     if ($validator->fails()) {
    //         $validationMessages = config('validationMessages.regex');
    //         $errors = $validator->errors()->getMessages();
    //         foreach ($errors as $key => $value) {
    //             $errors[$key] = $validationMessages[$key] ?? $value;
    //         }
    //         return [
    //             'errors' => true,
    //             'error_messages' => $errors,
    //         ];
    //     }
    //     return [
    //         'errors' => false,
    //         'data' => $input,
    //     ];
    // }
}
