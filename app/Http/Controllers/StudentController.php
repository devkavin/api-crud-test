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

    public function index()
    {
        $students = Student::all();
        if ($students->isEmpty()) {
            return APIHelper::makeAPIResponse(false, "Sorry, students data is empty", null, APIHelper::HTTP_NO_DATA_FOUND);
        } else {
            return APIHelper::makeAPIResponse(true, "Students data found", $students, APIHelper::HTTP_CODE_SUCCESS);
        }
    }

    // to store data
    public function store(Request $request)
    {
        $student = Student::create([
            'name' => $request->name,
            'email' => $request->email,
            // phone is char so it must be casted to string to store 0 in front
            'phone' => (string)$request->phone,
            'age' => (int)$request->age,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null
        ]);

        // validation schema to validate request and return error messages
        $validation_schema = [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits_between:10,12',
            'age' => 'required|numeric|digits_between:1,3'
        ];

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }


        if ($student) {
            return APIHelper::makeAPIResponse(true, "Student data is successfully added", $student, APIHelper::HTTP_CODE_SUCCESS);
            // 201 is status code for created
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
            // 200 is status code for ok
        } else {
            // return APIHelper::makeAPIResponse(false, "This is a Text response", null, APIHelper::HTTP_CODE_BAD_REQUEST);
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to retrieve, ID not found", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    // to update data
    // only has to update the data that is changed
    public function update(Request $request, $id)
    {
        $validation_schema = [
            //not required because it is not required to be changed
            'name' => 'nullable',
            'email' => 'nullable|email',
            'phone' => 'nullable|numeric|digits_between:10,12',
            'age' => 'nullable|numeric|digits_between:1,3'
        ];

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $student = Student::find($id);
        if ($student) {
            $data = $request->only([
                'name', 'email', 'phone', 'age', 'updated_at' => date('Y-m-d H:i:s')
            ]);

            // to store old value from database
            $oldValues = [];
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $oldValues[$key] = $student->{$key};
                }
            }
            // save the new values
            $student->fill($data);

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
        if ($student->isEmpty()) {
            return APIHelper::makeAPIResponse(false, "Sorry, student data failed to retrieve, name not found", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        } else {
            return APIHelper::makeAPIResponse(true, "Student data is successfully retrieved", $student, APIHelper::HTTP_CODE_SUCCESS);
        }
    }
}
