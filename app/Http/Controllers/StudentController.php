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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // test get function
    public function test($id)
    {
        $student = Student::find($id);

        return $student->image_url;
    }

    // documentation
    public function postImage(Request $request)
    {
        $student = Student::find($request->id);
        // NEXT ADD DELETE VALIDATION
        // VALIDATION IF IMAGE ALREADY EXISTS IN THE DATABASE FOR THE STUDENT
        // validate the request using the validateRequest function in ImageHelper
        $validation_schema  = config('validationSchemas.image');
        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        if ($student->image_url) {
            ImageHelper::deleteImage($student);
        }

        // $student = ImageHelper::createImageUrl($request, "Test");
        $image_url          = ImageHelper::createImageUrl($request, $student->name);
        $student->image_url = $image_url;
        $student->save();

        // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student->image_url, APIHelper::HTTP_CODE_SUCCESS);
        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
    }

    public function deleteImage(Request $request)
    {
        $student = Student::find($request->id);
        if ($student->image_url == null) {
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.action'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        ImageHelper::deleteImage($student);
        ImageHelper::updateDatabaseImageUrl($student, null);
        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
    }

    /**
     * Search data by date and paginate the response
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $query = Student::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereBetween('created_at', [$search, $search]);
        }

        $limit = $request->limit;
        $page  = $request->page;
        $total = $query->count();
        if ($limit == 0) {
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $query->get(), config('statusCodes.HTTP_CODE_SUCCESS'));
        }

        // paginate the response
        $responseData           = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        // format the response dates to Y-m-d H:i:s
        $responseData           = APIHelper::formatDates($responseData->toArray(), StudentConstants::DATE_TIME_FORMAT);
        $paginatedResponseData  = APIHelper::createPaginatedResponse($responseData, $total, $page, $limit);

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedResponseData, config('statusCodes.HTTP_CODE_SUCCESS'));
    }

    /**
     * Find by id
     * @param $id
     * @return mixed
     * @throws Exception
     */
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

    /**
     * Store data
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $apiHelper          = new APIHelper();
        $validation_schema  = config('validationSchemas.student.store');
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
            $student                  = new Student();
            $requestData              = $apiHelper->getStoreStudentData($request);
            $image_url                = ImageHelper::createImageUrl(request(), $request->name);
            $requestData['image_url'] = $image_url;
            $student                  = $student->create($requestData);

            return APIHelper::makeAPIResponse(true, config('validationMessages.success.store'), [], APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    /**
     * Update data
     * TODO: Check image upload and update
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $apiHelper         = new APIHelper();
        $validation_schema = config('validationSchemas.student.update');

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        try {
            $student = Student::where('email', $request->email)->first();
            if (!$student) {
                throw new Exception(config('validationMessages.not_found.update'));
            }

            $requestData              = $apiHelper->getUpdateStudentData($request);
            $image_url                = ImageHelper::createImageUrl(request(), $request->name);
            $requestData['image_url'] = $image_url;
            $student->update($requestData);

            return APIHelper::makeAPIResponse(true, config('validationMessages.success.update'), [], APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    /**
     * delete data
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        // issue was the findorfail function
        $student = Student::find($id);
        if ($student) {
            ImageHelper::deleteImage($student);
            $student->delete();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.delete'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.delete'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    /**
     * restore data
     * @param $id
     * @return mixed
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->find($id);
        if ($student) {
            $student->restore();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.restore'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.restore'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    /**
     * force delete data
     * @param $id
     * @return mixed
     */
    public function forceDelete($id)
    {
        $student = Student::onlyTrashed()->find($id);
        if ($student) {
            ImageHelper::deleteImage($student);
            $student->forceDelete();
            // delete the image from the storage
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.force_delete'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.force_delete'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
