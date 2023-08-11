<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function test()
    {
        return APIHelper::makeAPIResponse(false, "This is a Text response", null, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function index()
    {
        $teachers = Teacher::all();
        return APIHelper::makeAPIResponse(true, "Success message", $teachers, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function store(Request $request)
    {
        $validation_schema = [
            'name'      => 'required',
            'email'     => 'required|email|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
            'phone'     => 'required|numeric|regex:/^0[0-9]{9,11}$/',
            'age'       => 'required|numeric|digits_between:1,3',
            'department' => 'required|'
        ];

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $teacher = Teacher::where('email', $request->email)->first();
        if ($teacher) {
            return APIHelper::makeAPIResponse(false, "Sorry, teacher data failed to add, email already exists", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $teacher = Teacher::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'age'        => (int)$request->age,
            'department' => $request->department,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null
        ]);

        if ($teacher) {
            return APIHelper::makeAPIResponse(true, "Success message", $teacher, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, "Sorry, teacher data failed to add", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }
}
