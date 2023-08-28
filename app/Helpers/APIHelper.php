<?php

namespace App\Helpers;

use App\Constants\StudentConstants;
use App\Constants\Constants;
use DateTime;
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
            Constants::COMMON_UPDATED_AT,
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

    // document the function
    /**
     * @param $data
     * @param string $dateFormat (default: Y-m-d H:i:s)
     * @return mixed
     */
    public static function formatDates($data, $dateFormat = 'Y-m-d H:i:s')
    {
        $collection = collect($data);

        // Confirm with Dhanuka ayya if it's required to format dates for a single record
        // if there is only one record
        if (isset($data['id'])) {
            $collection['created_at'] = Carbon::parse($data['created_at'])->format($dateFormat);
            $collection['updated_at'] = Carbon::parse($data['updated_at'])->format($dateFormat);
            return $collection;
        } else {
            $collection->transform(function ($student) use ($dateFormat) {
                $student['created_at'] = Carbon::parse($student['created_at'])->format($dateFormat);
                $student['updated_at'] = Carbon::parse($student['updated_at'])->format($dateFormat);
                return $student;
            });
        }
        return $collection;
    }

    // REF: MFAISAA-BFF\app\Helpers\APIHelper.php
    /**
     * Validate requests against schema
     * @param $schema
     * @param $request
     * @param string $type (default: insert)
     * @return array
     */
    public static function validateRequest($schema, $request, $type = 'insert')
    {
        // Get schema keys into a array (for validation)
        $schema_keys    = array_keys($schema);
        $input          = $request;
        // Only get full request object when creating a new record (insert) and remove unnecessary fields (only)
        if ($type == 'insert') {
            $input = $request->only($schema_keys);
        }

        $validator      = Validator::make($input, $schema);
        if ($validator->fails()) {
            $validationMessages     = config('validationMessages.regex');
            $errors                 = $validator->errors()->getMessages();
            foreach ($errors as $key => $value) {
                $errors[$key]   = $validationMessages[$key] ?? $value;
            }
            return [
                'errors'            => true,
                'error_messages'    => $errors,
            ];
        }
        return [
            'errors'        => false,
            'data'          => $input
        ];
    }

    // make ApiResponse
    /**
     * @param bool $status
     * @param string $message (default: success)
     * @param array $data is the data to be sent in the response (default: [])
     * @param int $status_code 
     * @return \Illuminate\Http\JsonResponse
     */
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
