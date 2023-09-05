<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Validator;

class ImageHelper
{
    /**
     * Create the image url
     * @param Request $request
     * @param string $imagePrefix
     * @param string $subFolder
     * @return string|null
     */
    public static function createImageUrl(Request $request, $imagePrefix = 'defaultImagePrefix', $subFolder = 'defaultSubFolder')
    {
        // if image is not in the request, return null
        if (!$request->hasFile('image')) {
            return null;
        }
        // get the image file from the request
        $imageFile = $request->file('image');

        // Storage::setVisibility('public/images/' . $imageFilename, 'public');
        // ask dhanuka ayya about the file name format
        // for the time being, model name and current timestamp is used with time()
        $imageFileNameWithExtension = $imagePrefix . '_' . time() . '.' . $imageFile->extension();
        // save the image to storage
        // path inside subFolder (images)
        $path = $subFolder . '/' . $imageFileNameWithExtension;
        Storage::disk('images')->put($path, File::get($imageFile));
        $image_url = $imageFileNameWithExtension;
        return $image_url;
    }

    /**
     * delete from storage
     * @param $model
     * @return mixed
     */
    public static function deleteImage($model, $subFolder = 'defaultSubFolder')
    {
        // get the image url from the model
        $imageName = $model->image_url;

        $path = $subFolder . '/' . $imageName;
        // Storage::delete($imageUrl);
        Storage::disk('images')->delete($path);
        return $model;
    }

    public function softDeleteImage($model, $subFolder = 'defaultSubFolder')
    {
        // get the image url from the model
        $imageUrl = $model->image_url;
        $imagePathInStorage = $subFolder . '/' . $imageUrl;
        // move the image to the deleted images folder
        Storage::disk('images')->move($imagePathInStorage, 'deletedImages/' . $imageUrl);
    }

    /**
     * update database image url
     * @param $model
     * @param string|null $image_url
     * @return mixed
     */
    public static function updateDatabaseImageUrl($model, $image_url = null)
    {
        // save the image url to the model
        $model->image_url = $image_url;
        $model->save();
        return $model;
    }
}
