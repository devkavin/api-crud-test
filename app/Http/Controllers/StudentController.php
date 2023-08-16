<?php

namespace App\Http\Controllers;

use App\Constants\StudentConstants;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Helpers\APIHelper;


class StudentController extends Controller
{
    // test get function
    public function test()
    {
        return APIHelper::makeAPIResponse(true, config('validationMessages.test_response'), null, config('statusCodes.HTTP_CODE_SUCCESS'));
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
        // regular response
        $SearchResponse = APIHelper::createResponseData($query->get());

        // paginated response
        // $paginatedSearchData = APIHelper::createPaginatedResponseData($query, $request);

        // $formattedResponseData = APIHelper::formatDates($paginatedSearchData, StudentConstants::DATE_TIME_FORMAT);
        // return $formattedResponseData;

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $SearchResponse, config('statusCodes.HTTP_CODE_SUCCESS'));
        // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedSearchData, config('statusCodes.HTTP_CODE_SUCCESS'));
        // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $formattedResponseData, config('statusCodes.HTTP_CODE_SUCCESS'));
    }











    // to store data
    public function store(Request $request)
    {
        $apiHelper          = new APIHelper();
        // validation schema to validate request and return error messages
        $validation_schema  = config('validationSchemas.student.store');

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, false, $validator['error_messages'], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        // check if email already exists
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, config('validationMessages.exist.store'), $student, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $requestData        = $apiHelper->getStoreStudentData($request);
        $student            = Student::create($requestData);
        $student            = $student->toArray();
        // $studentResponse    = APIHelper::createResponseData($student);
        // return $student;
        // return $studentResponse;
        if ($student) {

            return APIHelper::makeAPIResponse(true, config('validationMessages.success.store'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.store'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // get student by id
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student = $student->toArray();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.not_found.retrieve'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
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
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, config('validationMessages.exist.store'), $student, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        $student = Student::find($id);
        if ($student) {
            $requestData    = $apiHelper->getUpdateStudentData($request);
            $oldValues      = [];
            $oldValues      = APIHelper::getOldValues($student, $requestData);
            $student->update($requestData);
            $changes        = [];
            foreach ($requestData as $key => $value) {
                if ($value !== null) {
                    if ($value !== $oldValues[$key]) {
                        $changes[$key] = [
                            'old' => $oldValues[$key],
                            'new' => $value
                        ];
                    }
                }
            }
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.update'), $student, APIHelper::HTTP_CODE_SUCCESS, $changes);
        } else {
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.update'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to delete data
    public function destroy($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->delete();
            return APIHelper::makeAPIResponse(true, config('validationMessages.success.delete'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, config('validationMessages.failed.delete'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
