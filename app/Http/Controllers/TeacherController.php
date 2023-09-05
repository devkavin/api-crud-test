<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Helpers\ImageHelper;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function test()
    {
        return "Teacher test";
    }

    public function index(Request $request)
    {
        $query = Teacher::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereBetween('created_at', [$search, $search]);
        }

        $limit = $request->limit;
        $page  = $request->page;
        $total = $query->count();
        if ($limit == 0) {
            $responseData = $query->get();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $responseData, APIHelper::HTTP_CODE_SUCCESS);
        }

        // paginate the response
        $responseData           = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        // format the response dates to Y-m-d H:i:s
        $responseData           = APIHelper::formatDates($responseData->toArray(), 'Y-m-d H:i:s');
        $paginatedResponseData  = APIHelper::createPaginatedResponse($responseData, $total, $page, $limit);

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedResponseData, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function show($id)
    {
        $teacher = Teacher::find($id);
        if ($teacher) {
            $teacher = APIHelper::formatDates($teacher, 'Y-m-d H:i:s');
            return APIHelper::makeAPIResponse(true, "Success message", $teacher, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, teacher data not found", [], APIHelper::HTTP_NO_DATA_FOUND);
        }
    }
    public function store(Request $request)
    {
        $validation_schema  = config('validationSchemas.teacher.store');
        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        try {
            $teacher = Teacher::where('email', $request->email)->first();
            if ($teacher) {
                return APIHelper::makeAPIResponse(false, config('validationMessages.exist.store'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
            }
            $teacher = new Teacher();
            $requestData = $request->only(
                [
                    'name',
                    'email',
                    'phone',
                    'age',
                    'department',
                ],
            );
            $image_url                  = ImageHelper::createImageUrl(request(), $request->name, 'teachers');
            $requestData['image_url']   = $image_url;
            $teacher->create($requestData);
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        $validation_schema = config('validationSchemas.teacher.update');
        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        try {
            $teacher = Teacher::where('email', $request->email)->first();
            if (!$teacher) {
                return APIHelper::makeAPIResponse(false, config('validationMessages.not_found.update'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
            }
            $requestData = $request->only(
                [
                    'name',
                    'email',
                    'phone',
                    'age',
                    'department',
                ],
            );
            if ($teacher->image_url) {
                ImageHelper::deleteImage($teacher, 'teachers');
            }
            if (!$request->name) {
                $imagePrefix = $teacher->name;
            } else {
                $imagePrefix = $request->name;
            }
            $image_url                  = ImageHelper::createImageUrl(request(), $imagePrefix, 'teachers');
            $requestData['image_url']   = $image_url;
            $teacher->update($requestData);
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    public function delete($id)
    {
        $teacher = Teacher::find($id);
        if ($teacher) {
            ImageHelper::deleteImage($teacher, 'teachers');
            $teacher->delete();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.not_found.delete'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    public function restore($id)
    {
        $teacher = Teacher::withTrashed()->find($id);
        if ($teacher) {
            $teacher->restore();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.not_found.restore'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
