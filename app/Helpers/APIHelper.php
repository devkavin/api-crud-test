<?php

namespace App\Helpers;

use App\Constants\StudentConstants;
use App\Constants\Constants;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
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

    public static function getSearchParams($request)
    {
        $params = [
            Constants::SEARCH_PARAM_START_DATE => $request->startDate,
            Constants::SEARCH_PARAM_END_DATE   => $request->endDate,
            StudentConstants::STUDENT_COURSE   => $request->course,
        ];
        return $params;
    }

    public function getStoreStudentData($request)
    {
        $requestData = [
            Constants::COMMON_NAME              => $request->name,
            Constants::COMMON_EMAIL             => $request->email,
            Constants::COMMON_PHONE             => $request->phone,
            Constants::COMMON_AGE               => $request->age,
            StudentConstants::STUDENT_COURSE    => $request->course,
            Constants::COMMON_CREATED_AT        => date(Constants::DATE_TIME_FORMAT),
            Constants::COMMON_UPDATED_AT        => date(Constants::DATE_TIME_FORMAT),
        ];
        return $requestData;
    }

    public function getupdateStudentData($request)
    {
        $requestData = $request->only([
            Constants::COMMON_NAME,
            Constants::COMMON_EMAIL,
            Constants::COMMON_PHONE,
            Constants::COMMON_AGE,
            StudentConstants::STUDENT_COURSE,
        ]);
        return $requestData;
    }

    public static function createPaginatedResponse($data = null, $total = null, $page = null, $limit = null)
    {
        $paginatedResponse = [
            Constants::RESPONSE_DATA_KEY     => $data,
            Constants::RESPONSE_TOTAL_KEY    => $total,
            Constants::RESPONSE_PAGE_KEY     => $page,
            Constants::RESPONSE_LIMIT_KEY    => $limit,
        ];
        return $paginatedResponse;
    }

    public static function formatDates($data, $dateFormat = 'Y-m-d H:i:s')
    {
        foreach ($data as $key => $value) {
            $data[$key]['created_at'] = Carbon::parse($value['created_at'])->format($dateFormat);
            $data[$key]['updated_at'] = Carbon::parse($value['updated_at'])->format($dateFormat);
        }
        return $data;
    }

    // REF: MFAISAA-BFF\app\Helpers\APIHelper.php
    public static function validateRequest($schema, $request, $type = 'insert')
    {
        // Get schema keys into a array (for validation)
        $schema_keys = array_keys($schema);

        // If the request is not create, $request will take passed data
        $input = $request;
        // Only get full request object when creating
        // Ignore when doing the update
        if ($type == 'insert') {
            // Remove unnecessary fields from request
            $input = $request->only($schema_keys);
        }

        $validator = Validator::make($input, $schema);

        if ($validator->fails()) {
            $validationMessages     = config('validationMessages.regex');
            $errors                 = $validator->errors()->getMessages();
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

    public static function makeAPIResponse($status = true, $message = "success", $data = [], $status_code = self::HTTP_CODE_SUCCESS)
    {
        // $response = self::createResponseHead($status, $message, $status_code);
        $response = [
            Constants::RESPONSE_STATUS_KEY      => $status,
            Constants::RESPONSE_MESSAGE_KEY     => $message,
            Constants::RESPONSE_STATUS_CODE_KEY => $status_code,
        ];

        $response["data"] = $data;

        return response()->json($response, $status_code);
    }
}
