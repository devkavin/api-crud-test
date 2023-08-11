<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Helpers\APIHelper;


class StudentController extends Controller
{
    // test get function
    public function test()
    {
        return APIHelper::makeAPIResponse(false, "This is a Text response", null, APIHelper::HTTP_CODE_BAD_REQUEST);
    }

    public function index(Request $request)
    {
        $students   = Student::all();
        // $students   = Student::all()->toArray();
        // $apiHelper  = new APIHelper();
        // $students   = $apiHelper->paginateResponse($students, $request);
        // return dd($students);
        return APIHelper::makeAPIResponse(true, "Success message", $students, APIHelper::HTTP_CODE_BAD_REQUEST);
    }


    // to store data
    public function store(Request $request)
    {
        // validation schema to validate request and return error messages
        $validation_schema = [
            'name'      => 'required',
            'email'     => 'required|email|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
            'phone'     => 'required|numeric|regex:/^0[0-9]{9,11}$/',
            'age'       => 'required|numeric|digits_between:1,3'
        ];

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        // check if email already exists
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to add, email already exists", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        $student = Student::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'age'        => (int)$request->age,
            // stored in Y-m-d H:i:s format
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null
        ]);

        if ($student) {
            return APIHelper::makeAPIResponse(true, "Student data is successfully added", $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to add", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // get student by id
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            return APIHelper::makeAPIResponse(true, "Student data is successfully retrieved", $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to retrieve, ID not found", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to update data
    // only has to update the data that is changed
    public function update(Request $request, $id)
    {
        $validation_schema = [
            //not required because it is not required to be changed
            'name'       => 'nullable',
            'email'      => 'nullable|email',
            'phone'      => 'nullable|numeric|digits_between:10,12',
            'age'        => 'nullable|numeric|digits_between:1,3'
        ];

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        // check if email already exists
        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to update, email already exists", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
        $student = Student::find($id);
        if ($student) {
            // only() is used to get only the specified keys from the request,
            // unwanted keys will be ignored
            $data = $request->only([
                'name',
                'email',
                'phone',
                'age',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // to store old value from database
            $oldValues = [];
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $oldValues[$key] = $student->{$key};
                }
            }

            // save the new values
            $student->update($data);

            // to store the changes
            $changes = [];
            foreach ($data as $key => $value) {
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
            return APIHelper::makeAPIUpdateResponse(true, "Student data is successfully updated", $student, $changes, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to update", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to delete data
    public function destroy($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->delete();
            return APIHelper::makeAPIResponse(true, "Student data is successfully deleted", $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            // if the id is not found
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to delete", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to search data
    public function search($name)
    {
        $student = Student::where('name', 'like', '%' . $name . '%')->get();
        if ($student) {
            return APIHelper::makeAPIResponse(true, "Student data is successfully retrieved", $student, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to retrieve, name not found", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
