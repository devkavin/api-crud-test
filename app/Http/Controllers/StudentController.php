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
        return APIHelper::makeAPIResponse(false, config('validationMessages.Test_response'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
    }

    public function index(Request $request)
    {
        $query = Student::query();

        // // if the request has a search parameter
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereBetween('created_at', [$search, $search]);
        }

        $paginatedSearchData = APIHelper::createPaginatedResponseData($query, $request);
        // $paginatedSearchData = APIHelper::createResponseData($query->get());

        // $formattedResponseData = APIHelper::formatDates($paginatedSearchData, StudentConstants::DATE_TIME_FORMAT);

        return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $paginatedSearchData, config('statusCodes.HTTP_CODE_SUCCESS'));
        // return APIHelper::makeAPIResponse(true, config('validationMessages.success.action'), $formattedResponseData, config('statusCodes.HTTP_CODE_SUCCESS'));
    }

    // to store data
    public function store(Request $request)
    {
        $apiHelper          = new APIHelper();
        $model              = new Student();
        // validation schema to validate request and return error messages
        $validation_schema  = config('validationSchemas.student.store');

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, false, $validator['error_messages'], APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        // check if email already exists
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.exist.store'), $student, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        //
        // called as an instance method because an instance is created in the APIHelper class instead of a static method
        // static methods are called using the class name because they are not instantiated and are directly called
        //
        $requestData        = $apiHelper->getStoreStudentData($request);
        $student            = Student::create($requestData);
        $student            = $student->toArray();
        if ($student) {
            return APIHelper::makeAPIResponse(true, false, config('validationMessages.success.store'), $model, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.failed.store'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // get student by id
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student = $student->toArray();
            return APIHelper::makeAPIResponse(true, false, config('validationMessages.success.action'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.not_found.retrieve'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
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
            return APIHelper::makeAPIResponse(false, false, $validator['error_messages'], APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        // check if email already exists
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.exist.store'), $student, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        $student = Student::find($id);
        if ($student) {

            // created a custom update function use the only() function to get only the keys that are specified
            $requestData    = $apiHelper->getUpdateStudentData($request);

            // // only() is used to get only the specified keys from the request,
            // // unwanted keys will be ignored
            // $data = $request->only([
            //     'name',
            //     'email',
            //     'phone',
            //     'age',
            // ]);
            // create custom function to store old value from database
            $oldValues = [];
            $oldValues = APIHelper::getOldValues($student, $requestData);
            // foreach ($requestData as $key => $value) {
            //     if ($value !== null) {
            //         $oldValues[$key] = $student->{$key};
            //     }
            // }

            // save the new values
            $student->update($requestData);

            // create custom function to store the changes

            $changes = [];
            foreach ($requestData as $key => $value) {
                if ($value !== null) {
                    // if value is not null and the old value is different, then add it to the changes
                    if ($value !== $oldValues[$key]) {
                        $changes[$key] = [
                            'old' => $oldValues[$key],
                            'new' => $value
                        ];
                    }
                }
            }
            return APIHelper::makeAPIUpdateResponse(true, config('validationMessages.success.update'), $student, $changes, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.failed.update'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to delete data
    public function destroy($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->delete();
            return APIHelper::makeAPIResponse(true, false, config('validationMessages.success.delete'), $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, false, config('validationMessages.failed.delete'), null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
