<?php

namespace App\Helpers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageHelper
{

    public function postImage(Request $request, $model)
    {
        // get the image file from the request
        $imageFile = $request->file('image');

        // ask dhanuka ayya about the file name format
        // for the time being, current timestamp is used with time()
        $imageFilename = time() . '.' . $imageFile->extension();
        $imageFile->move(public_path('images'), $imageFilename);

        // save the image url to the model
        $model->image_url = '/images/' . $imageFilename;
        $model->save();

        return $model;
    }

    public function deleteImage($model)
    {
        // get the image url from the model
        $imageUrl = public_path() . $model->image_url;

        // delete the image from the storage
        Storage::delete($imageUrl);

        // delete the image url from the model
        $model->image_url = null;
        $model->save();

        return $model;
    }


    public static function validateRequest($schema, $request, $type = 'insert')
    {
        // Get schema keys into a array (for validation)
        $schema_keys = array_keys($schema);
        $input = $request;

        // Only get full request object when creating a new record (insert) and remove unnecessary fields (only)
        // image is a file, so we need to check it against the schema

        if ($type == 'insert') {
            $input = $request->only($schema_keys);
        }

        $validator = Validator::make($input, $schema);
        if ($validator->fails()) {
            $validationMessages = config('validationMessages.image');
            $errors = $validator->errors()->getMessages();
            foreach ($errors as $key => $value) {
                $errors[$key] = $validationMessages[$key] ?? $value;
            }
            return [
                'errors' => true,
                'error_messages' => $errors,
            ];
        }
        return [
            'errors' => false,
            'data' => $input,
        ];
    }
}
