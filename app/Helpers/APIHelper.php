<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class APIHelper
{
    // Taken from: MFAISAA-BFF\app\Helpers\APIHelper.php
    const HTTP_CODE_SUCCESS          = 200;
    const HTTP_CODE_SERVER_ERROR     = 500;
    const HTTP_CODE_BAD_REQUEST      = 400;
    const HTTP_CODE_UNAUTHERIZED     = 401;
    const HTTP_NO_DATA_FOUND         = 204;
    const FUNCTIONAL_ERROR_COMDE     = 501;
    const PERMISSION_ERROR           = 502;
    const HTTP_CODE_BAD_AUTH_REQUEST = 403;
    const INVALID_DATA               = 422;


    // TODO: check what @param are for
    // @params are used to define the type of the variable
    /**
     * @param bool $status
     * @param bool $paginated
     * @param string $message
     * @param int $status_code
     * @param !null $data
     * @param int $status_code
     * @param int $page
     * @param int $limit
     */

    public static function makeAPIResponse($status = true, $paginated = false, $message = "success", $data = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        // $apiHelper is not used because the function is static, which means it can be called without creating an instance of the class first
        // only place where you have to create an instance of the class first is when you want to call a non-static function like paginateResponse
        // paginate response is non
        // $apiHelper        = new APIHelper();
        $refinedData      = self::getRefinedData($data);
        $response         = self::getResponse($refinedData, $status, $message, $status_code);
        if ($paginated) {
            $paginatedData      = APIHelper::paginateResponse($refinedData, request());
            $response["data"]   = $paginatedData;
        } else {
            $response["data"]   = $refinedData;
        }
        return response()->json($response, $status_code);
    }

    // Used Carbon extension to format date
    // REF: https://carbon.nesbot.com/docs/
    public static function getRefinedData($data)
    {
        $refinedData = $data;
        // fix Invalid argument supplied for foreach() error
        if (!is_array($refinedData)) {
            return $refinedData;
        }

        foreach ($refinedData as $key => $value) {
            if (isset($value["created_at"])) {
                $refinedData[$key]["created_at"] = Carbon::parse($value["created_at"])->format('Y-m-d H:i:s');
            }
            if (isset($value["updated_at"])) {
                $refinedData[$key]["updated_at"] = Carbon::parse($value["updated_at"])->format('Y-m-d H:i:s');
            }
        }
        return $refinedData;
    }
    //getStudentData function to retrieve data from request
    public function getStoreStudentData($request)
    {
        $requestData = [
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'age'        => $request->age,
            // stored in Y-m-d H:i:s format
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return $requestData;
    }
    public function getupdateStudentData($request)
    {
        $requestData = $request->only([
            'name',
            'email',
            'phone',
            'age',
        ]);
        return $requestData;
    }

    public static function getOldValues($student, $requestData)
    {
        foreach ($requestData as $key => $value) {
            if ($value !== null) {
                $oldValues[$key] = $student->{$key};
            }
        }
        return $oldValues;
    }

    public static function getResponse($data, $status = true, $message = "Success", $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = [
            "success"     => $status,
            "status_code" => $status_code,
            "message"     => $message,
        ];
        return $response;
    }

    public static function getPaginatedResponse($data = null, $total = null, $page = null, $limit = null)
    {
        $response = [
            "data"      => $data,
            "total"     => $total,
            "page"      => $page,
            "limit"     => $limit,
        ];
        return $response;
    }

    public static function paginateResponse($data, $request, $limit = null, $page = null)
    {
        if ($data == null || empty($data)) {
            return $data = [];
        }
        // get total items if data is not empty
        $page            = $request->page;
        $limit           = $request->limit;
        $total           = count($data);
        // if total is 0, return empty array
        if ($total == 0) {
            return $data = [];
        }
        if ($page == 0) {
            $page = 1;
        }
        // fix division by zero
        if ($limit == 0) {
            return $data;
        }
        if ($limit == 1) {
            return $data;
        }
        if ($page > ceil($total / $limit)) {
            return config('validationMessages.not_found.page');
        }
        if ($limit > $total) {
            return config('validationMessages.invalid.limit');
        }
        // if page is less than 1, return current page
        // paginator::resolvecurrentpage() returns the current page
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $currentPage    = $page;
        $offset         = ($currentPage * $limit) - $limit;
        // array slice to get the items to display
        $itemsToShow    = array_slice($data, $offset, $limit);
        // return dd($itemsToShow, $total, $limit);
        // if there is only 1 item, return it as an array
        if (count($itemsToShow) == 1) {
            $itemsToShow = $data;
        }
        // $apiHelper       = new APIHelper();
        $paginatedResponse         = self::getPaginatedResponse($itemsToShow, $total, $page, $limit);
        return $paginatedResponse;
    }

    // MAKE API DATA UPDATE RESPONSE
    public static function makeAPIUpdateResponse($status = true, $message = "Success", $data = null, $changes = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        // $apiHelper        = new APIHelper();
        $refinedData      = self::getRefinedData($data);
        $response         = self::getResponse($refinedData, $status, $message, $status_code);
        if ($data != null || is_array($refinedData)) {
            $response["data"] = $refinedData;
        }
        if ($changes != null || is_array($changes)) {
            $response["changes"] = $changes;
        }
        // make response
        return response()->json($response, $status_code);
    }

    // REQUEST VALIDATION FOR CREATE AND UPDATE
    // Taken and modified from: MFAISAA-BFF\app\Helpers\APIHelper.php
    public static function validateRequest($schema, $request, $type = 'insert')
    {
        // Get schema keys into a array (for validation)
        $schema_keys = array_keys($schema);

        // If the request is not and create, $request will take passed data
        $input = $request;
        // Only get full request object when creating
        // Ignore when doing the update
        if ($type == 'insert') {
            // Remove unnecessary fields from request
            $input = $request->only($schema_keys);
        }

        // // Validate data fields against schema
        $validator = Validator::make($input, $schema);

        // // OLD VALIDATOR which uses the errors() method
        // if ($validator->fails()) {
        //     // Return validation errors, if something went wrong
        //     if ($validator->fails()) {
        //         return ['errors' => true, 'error_messages' => $validator->errors()];
        //     }
        // }

        if ($validator->fails()) {
            // Get validation messages for the regex rule from the validation messages config file
            $validationMessages = config('validationMessages.regex');
            $errors = $validator->errors()->getMessages();

            // Iterate over the errors array and get the validation message for each error
            foreach ($errors as $key => $value) {
                $errors[$key] = $validationMessages[$key] ?? $value;
            }

            return [
                'errors'            => true,
                'error_messages'    => $errors,
            ];
        }


        return ['errors' => false, 'data' => $input];
    }

    public static function checkRequest($request)
    {
        // TODO: Check whether the request is filtering dates, course, or date
    }
}
