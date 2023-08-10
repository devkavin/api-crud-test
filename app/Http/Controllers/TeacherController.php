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
        $teachers = Teacher::paginate(2);
        if ($teachers->isEmpty()) {
            return APIHelper::makeAPIResponse(false, "Sorry, teachers data is empty", null, APIHelper::HTTP_NO_DATA_FOUND);
        } else {
            return APIHelper::makeAPIResponse(true, "Teachers data found", $teachers, APIHelper::HTTP_CODE_SUCCESS);
        }
    }
}