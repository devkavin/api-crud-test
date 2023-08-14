<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function test()
    {
        return APIHelper::makeAPIResponse(false, false, config('validationMessages.Test_response'), null, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function index()
    {
        $teachers = Teacher::all()->toArray();
        // to paginate the response
        // $teachers = $teachers->toArray();
        return APIHelper::makeAPIResponse(true, true, config('validationMessages.success.action'), $teachers, APIHelper::HTTP_CODE_SUCCESS);
    }

    public function store(Request $request)
    {

        $validation_schema = config('validationSchemas.teacher.store');

        $validator = APIHelper::validateRequest($validation_schema, $request);
        if ($validator['errors']) {
            return APIHelper::makeAPIResponse(false, $validator['error_messages'], null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }

        $teacher = Teacher::where('email', $request->email)->first();
        if ($teacher) {
            return APIHelper::makeAPIResponse(false, false, "Sorry, teacher data failed to add, email already exists", null, APIHelper::HTTP_CODE_BAD_REQUEST);
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
        $teacher = $teacher->toArray();
        if ($teacher) {
            return APIHelper::makeAPIResponse(true, false, "Success message", $teacher, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, false, "Sorry, teacher data failed to add", null, APIHelper::HTTP_CODE_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        $teacher = Teacher::find($id);
        if ($teacher) {
            $teacher = $teacher->toArray();
            return APIHelper::makeAPIResponse(true, false, "Success message", $teacher, APIHelper::HTTP_CODE_SUCCESS);
        } else {
            return APIHelper::makeAPIResponse(false, false, "Sorry, teacher data not found", null, APIHelper::HTTP_NO_DATA_FOUND);
        }
    }
}
