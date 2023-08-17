<?php

namespace App\Http\Controllers;

use App\Constants\StudentConstants;
use App\Constants\Constants;
use Exception;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Helpers\APIHelper;


class StudentController extends Controller
{
    // test get function
    public function test()
    {
        return APIHelper::makeAPIResponse(true, config('validationMessages.test_response'), [], config('statusCodes.HTTP_CODE_SUCCESS'));
    }

    public function index(Request $request)
    {
        $query = Student::query();
        // TODO: Create a function to handle the search
        // if the request has a search parameter
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereBetween('created_at', [$search, $search]);
        }

        $limit                  = $request->limit;
        $page                   = $request->page;
        $total                  = $query->count();
        // paginate the response
        $responseData           = $query->limit($limit)->offset(($page - 1) * $limit)->get();
        // format the response dates to Y-m-d H:i:s
        $responseData           = APIHelper::formatDates($responseData->toArray(), StudentConstants::DATE_TIME_FORMAT);
        $paginatedResponseData  = APIHelper::createPaginatedResponse($responseData, $total, $page, $limit);

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedResponseData, config('statusCodes.HTTP_CODE_SUCCESS'));
    }

    // public function index(Request $request)
    // {
    //     $query = Student::query();
    //     if ($request->has('search')) {
    //         $search = $request->search;
    //         $query->whereBetween('created_at', [$search, $search]);
    //     }
    //     // // regular response
    //     $SearchResponse = APIHelper::createResponseData($query->get());
    //     $SearchResponse = APIHelper::formatDates($SearchResponse->toArray(), StudentConstants::DATE_TIME_FORMAT);

    //     return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $SearchResponse, config('statusCodes.HTTP_CODE_SUCCESS'));
    // }

    // get student by id
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student = $student->toArray();
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

            // create the student data and store it in the database
            $requestData        = $apiHelper->getStoreStudentData($request);
            $student            = Student::create($requestData);
            $student            = $student->toArray();

            return APIHelper::makeAPIResponse(true, config('validationMessages.success.store'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } catch (Exception $e) {
            return APIHelper::makeAPIResponse(false, $e->getMessage(), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
    // public function store(Request $request)
    // {
    //     $apiHelper          = new APIHelper();
    //     $validation_schema  = config('validationSchemas.student.store');

    //     // validate the request for the required fields
    //     $validator = APIHelper::validateRequest($validation_schema, $request);
    //     if ($validator['errors']) {
    //         return APIHelper::makeAPIResponse(false, $validator['error_messages'], [], APIHelper::HTTP_CODE_BAD_REQUEST);
    //     }

    //     // check if email already exists
    //     $student = Student::where('email', $request->email)->first();
    //     if ($student) {
    //         return APIHelper::makeAPIResponse(false, config('validationMessages.exist.store'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
    //     }

    //     // create the student data and store it in the database
    //     $requestData        = $apiHelper->getStoreStudentData($request);
    //     $student            = Student::create($requestData);
    //     $student            = $student->toArray();

    //     if ($student) {
    //         return APIHelper::makeAPIResponse(true, config('validationMessages.success.store'), [], APIHelper::HTTP_CODE_SUCCESS);
    //     } else {
    //         return APIHelper::makeAPIResponse(false, config('validationMessages.failed.store'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
    //     }
    // }

    // to update data
    // only has to update the data that is changed
    public function update(Request $request, $id)
    {
        $apiHelper          = new APIHelper();
        $validation_schema  = config('validationSchemas.student.update');
        $validator = APIHelper::validateRequest($validation_schema, $request);

        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, config('validationMessages.exist.store'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $student = Student::find($id);
        if ($student) {
            $requestData    = $apiHelper->getUpdateStudentData($request);
            $student->update($requestData);
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.update'), [], APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.update'), [], APIHelper::HTTP_CODE_BAD_REQUEST);
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
