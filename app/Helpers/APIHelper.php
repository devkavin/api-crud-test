<?php

namespace App\Helpers;

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

    /**
     * @param bool $status
     * @param string $message
     * @param int $status_code
     * @param int $perPage
     * @param int $page
     * @param !null $data
     * @return \Illuminate\Http\JsonResponse
     */

    public static function makeAPIResponse($status = true, $message = "Success", $data = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = [
            "success"     => $status,
            "status_code" => $status_code,
            "message"     => $message,
        ];

        $apiHelper        = new APIHelper();
        $paginated_data   = $apiHelper->paginateResponse($data, request());
        $response["data"] = $paginated_data;
        // $response["data"] = $data;
        // return response
        return response()->json($response, $status_code);
    }

    // CUSTOM PAGINATION
    // public function paginateResponse($data, $request, $perPage = 4, $page = null)
    // {
    //     // if data is empty, return empty array
    //     if ($data == null || empty($data)) {
    //         $data = [];
    //     }
    //     $page       = $request->input('page');
    //     $perPage    = $request->input('limit');
    //     // if page is greater than total pages, return last page
    //     // if page is less than 1, return first page
    //     $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    //     $total          = count($data);
    //     $currentPage    = $page;
    //     if ($page > count($data) / $perPage) {
    //         $page = ceil(count($data) / $perPage);
    //     }
    //     $offset         = ($currentPage * $perPage) - $perPage;
    //     $itemsToShow    = array_slice($data, $offset, $perPage);
    //     // return dd($itemsToShow, $total, $perPage);
    //     return new LengthAwarePaginator($itemsToShow, $total, $perPage);
    // }
    public function paginateResponse($data, $request, $perPage = 4, $page = null)
    {
        $page            = $request->page;
        $perPage         = $request->limit;
        if ($data == null || empty($data)) {
            return $data = [];
        }
        $total           = count($data);
        if ($total == 0) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }
        // fix division by zero
        if ($perPage == 0) {
            $perPage = 1;
        }
        // if page is greater than total pages, return last page
        if ($page > ceil($total / $perPage)) {
            $page = ceil($total / $perPage);
        }
        // if page is less than 1, return first page
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $currentPage    = $page;
        $offset         = ($currentPage * $perPage) - $perPage;
        // array slice to get the items to display
        $itemsToShow    = array_slice($data, $offset, $perPage);
        // return dd($itemsToShow, $total, $perPage);

        // LengthAwarePaginator to make the pagination
        $paginated_data = [$itemsToShow, $total, $perPage, $page];
        return $paginated_data;
    }
    // MAKE API DATA UPDATE RESPONSE
    public static function makeAPIUpdateResponse($status = true, $message = "Success", $data = null, $changes = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = [
            "success"     => $status,
            "status_code" => $status_code,
            "message"     => $message,
        ];
        if ($data != null || is_array($data)) {
            $response["data"] = $data;
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

        // Validate data fields against schema
        $validator = Validator::make($input, $schema);

        if ($validator->fails()) {
            return ['errors' => true, 'error_messages' => $validator->errors()];
        }

        return ['errors' => false, 'data' => $input];
    }
}
