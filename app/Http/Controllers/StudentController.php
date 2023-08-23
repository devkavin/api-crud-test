<?php

namespace App\Http\Controllers;

use App\Constants\StudentConstants;
use App\Constants\Constants;
use App\Helpers\ImageHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Helpers\APIHelper;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // test get function
    public function test(Request $request)
    {
        $id = $request->id;
        // // check if the student has an image
        $student = Student::findOrFail($id);
        // if image is null, return false
        if ($student->image_url == null) {
            return APIHelper::makeAPIResponse(false, config('validationMessages.error.no_image'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        } else {
            // $imageHelper        = new ImageHelper();
            // $student            = $imageHelper->deleteImage($student);
            // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
            // get path to storage
            // $path = public_path('images');
            // $path = public_path() . $student->image_url;
            // path to storage

            // LAST EDIT HERE
            // PATH IN POSTMAN THROUGH CLICK NOT WORKING

            $path = Storage::putFile('images/', $request->file('image'));


            return $path;
        }
    }

    // public function postImage(Request $request)
    // {
    //     $student            = Student::findOrFail($request->id);
    //     $imageFile          = $request->file('image');
    //     // ask dhanuka ayya about the file name format
    //     // for the time being, current timestamp is used with time()
    //     $imageFilename      = time() . '.' . $student->name . $imageFile->extension();
    //     $imageFile->move(public_path('images'), $imageFilename);

    //     $student->image_url = '/images/' . $imageFilename;
    //     $student->save();
    //     return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
    // }


    // documentation
    public function postImage(Request $request)
    {
        $student = Student::findOrFail($request->id);
        // LAST EDIT HERE
        // NEXT ADD DELETE VALIDATION
        // VALIDATION IF IMAGE ALREADY EXISTS IN THE DATABASE FOR THE STUDENT
        // validate the request using the validateRequest function in ImageHelper
        $validation_schema  = config('validationSchemas.image');
        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        // $student = ImageHelper::createImageUrl($request, "Test");
        $image_url = ImageHelper::createImageUrl($request, $student->name);
        $student->image_url = $image_url;
        $student = ImageHelper::saveImage($student, $image_url);

        // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student->image_url, APIHelper::HTTP_CODE_SUCCESS);
        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function deleteImage(Request $request)
    {
        $student            = Student::findOrFail($request->id);
        $student            = ImageHelper::deleteImage($student);
        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function index(Request $request)
    {
        $query = Student::query();
        // if the request has a search parameter
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereBetween('created_at', [$search, $search]);
        }

        $limit                  = $request->limit;
        $page                   = $request->page;
        $total                  = $query->count();
        if ($limit == 0) {
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $query->get(), config('statusCodes.HTTP_CODE_SUCCESS'));
        }
        // paginate the response
        $responseData           = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        // format the response dates to Y-m-d H:i:s
        // $responseData           = APIHelper::formatDates($responseData, StudentConstants::DATE_TIME_FORMAT);
        $responseData           = APIHelper::formatDates($responseData->toArray(), StudentConstants::DATE_TIME_FORMAT);
        $paginatedResponseData  = APIHelper::createPaginatedResponse($responseData, $total, $page, $limit);

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedResponseData, config('statusCodes.HTTP_CODE_SUCCESS'));
    }

    // get student by id
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student = APIHelper::formatDates($student, StudentConstants::DATE_TIME_FORMAT);
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.not_found.retrieve'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to store data
    public function store(Request $request)
    {
        $apiHelper          = new APIHelper();
        $validation_schema  = config('validationSchemas.student.store');

        // validate the request for the required fields
        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        try {
            // check if email already exists
            $student = Student::where('email', $request->email)->first();
            if ($student) {
                throw new Exception(config('validationMessages.exist.store'));
            }
            // student object to add the image url
            $student = new Student();
            // create the student data and store it in the database
            $requestData              = $apiHelper->getStoreStudentData($request);
            // add request data to the student object
            $image_url                = ImageHelper::createImageUrl(request(), $request->name);
            $requestData['image_url'] = $image_url;
            $student                  = $student->create($requestData);
            $student                  = $student->toArray();

            return APIHelper::makeAPIResponse(true, config('validationMessages.success.store'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to update data
    // only has to update the data that is changed
    public function update(Request $request, $id)
    {
        $apiHelper          = new APIHelper();
        $validation_schema  = config('validationSchemas.student.update');
        $validator = APIHelper::validateRequest($validation_schema, $request);

        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        try {
            $student = Student::where('email', $request->email)->first();
            if ($student) {
                throw new Exception(config('validationMessages.exist.update'));
            }

            $student = Student::find($id);
            if (!$student) {
                throw new Exception(config('validationMessages.failed.update'));
            }

            $requestData    = $apiHelper->getUpdateStudentData($request);
            $student->update($requestData);
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.update'), [], APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
    // to delete data
    public function destroy($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->delete();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.delete'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.delete'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}