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

    public static function search($request, $model)
    {
        $params = self::getSearchParams($request);

        $startDate = $params['startDate'];
        $endDate   = $params['endDate'];
        $course    = $params['course'];


        if ($course && $startDate && $endDate) {
            // get all students between startDate and endDate for the course
            $students = $model->where('course', $course)->whereBetween('created_at', [$startDate, $endDate])->get()->toArray();
        } else if ($startDate && $endDate && $startDate < $endDate) {
            // get all students between startDate and endDate
            $students = $model->whereBetween('created_at', [$startDate, $endDate])->get()->toArray();
        } else if ($course) {
            $students = $model->where('course', $course)->get()->toArray();
        } else {
            $students = $model->all()->toArray();
        }
        $searchResult = $students;

        return $searchResult;
    }

    public static function createSearchQuery($query)
    {

        $startDateKey = config('constants.search.startDate');
        $endDateKey   = config('constants.search.endDate');
        $courseKey    = config('constants.search.course');

        // get keys from formats.php
        $startDateKey = (config('constants.search.startDate'));

        // switch case 
        switch ($query) {

                // if query contains startDateKey and endDateKey and courseKey
            case (isset($query[$startDateKey]) && isset($query[$endDateKey]) && isset($query[$courseKey])):
                $query->where($courseKey, $query[$courseKey])->whereBetween('created_at', [$query[$startDateKey], $query[$endDateKey]]);
                break;
        }

        if (isset($query[$startDateKey]) && isset($query[$endDateKey])) {
            $query->whereBetween('created_at', [$query[$startDateKey], $query[$endDateKey]]);
        }
        return $query;
    }

    public static function getSearchParams($request)
    {

        $startDateKey = config('constants.search.startDate');
        $endDateKey   = config('constants.search.endDate');
        $courseKey    = config('constants.search.course');

        $params = [
            $startDateKey => $request->startDate,
            $endDateKey   => $request->endDate,
            $courseKey    => $request->course,
        ];
        return $params;
    }

    public function getStoreStudentData($request)
    {
        $common         = config('constants.common');
        $formats        = config('formats');

        $requestData = [
            $common['name']           => $request->name,
            $common['email']          => $request->email,
            $common['phone']          => $request->phone,
            $common['age']            => $request->age,
            $common['course']         => $request->course,
            $common['created_at']     => date($formats['dateTime']),
            $common['updated_at']     => date($formats['dateTime']),
        ];
        return $requestData;
    }

    public function getupdateStudentData($request)
    {
        // LAST EDIT HERE
        $common         = config('constants.common');
        $commonKeys         = array_keys($common);
        $requestData = $request->only([
            $commonKeys
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

    public static function createResponseHead($status = true, $message = "Success", $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = [
            "success"     => $status,
            "status_code" => $status_code,
            "message"     => $message,
        ];
        return $response;
    }

    public static function createResponseData($data)
    {
        $dataArray = $data->toArray();
        $responseData = self::getRefinedData($dataArray);
        return $responseData;
    }

    public static function createPaginatedResponse($data = null, $total = null, $page = null, $limit = null)
    {
        $paginatedResponse = [
            "data"      => $data,
            "total"     => $total,
            "page"      => $page,
            "limit"     => $limit,
        ];
        return $paginatedResponse;
    }

    public static function createPaginatedResponseData($request, $model)
    {
        // custom pagination at database level using limit and offset
        $requestParams = self::getPaginationParams($request);
        $page   = $requestParams['page'];
        $limit  = $requestParams['limit'];
        $offset = $requestParams['offset'];
        // get paginated data
        $paginatedData = $model::query()->limit($limit)->offset($offset)->get()->toArray();
        $total = $model::count();
        // return paginated response
        $paginatedResponse = self::createPaginatedResponse($paginatedData, $total, $page, $limit);
        // format dates
        // $paginatedResponse = self::getRefinedData($paginatedResponse);
        return $paginatedResponse;
    }

    public static function getPaginationParams($request)
    {
        $page  = $request->page ?? 1;
        $limit = $request->limit ?? 3;
        $offset = ($page - 1) * $limit;
        $requestParams = [
            'page'   => $page,
            'limit'  => $limit,
            'offset' => $offset,
        ];
        return $requestParams;
    }

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

        // // Validate data fields against schema to check if they are valid
        $validator = Validator::make($input, $schema);

        if ($validator->fails()) {
            $validationMessages = config('validationMessages.regex');
            $errors = $validator->errors()->getMessages();
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

    public static function getRefinedData($data)
    {
        $refinedData = $data;
        // fix Invalid argument supplied for foreach() error
        if (!is_array($refinedData)) {
            return $refinedData;
        }
        // if data is array, format dates
        if (isset($refinedData['data'])) {
            // $refinedData['data'] = self::formatDates($refinedData['data'], config('formats.dateTime'));
            $refinedData['data'] = self::formatDates($refinedData['data'], config('formats.dateTime'));
        }

        // $refinedData = self::formatDates($refinedData, config('formats.dateTime'));
        return $refinedData;
    }

    public static function formatDates($data, $dateFormat = 'Y-m-d H:i:s', $datesToFormat = ['created_at', 'updated_at'])
    {
        // fix Invalid argument supplied for foreach() error
        // if (!is_array($data)) {
        //     return $data;
        // }
        // cannot be inside make api response

        // DB SORT 
        // filter, offset, limit
        foreach ($data as $key => $value) {
            foreach ($datesToFormat as $date) {
                // $data[$key]['created_at'] = 'test';
                $data[$key][$date] = Carbon::parse($value[$date])->format($dateFormat);
            }
        }
        // return $data;
        return $data;
    }

    public static function makeAPIResponse($status = true, $paginated = false, $message = "success", $model = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = self::createResponseHead($status, $message, $status_code);

        if ($paginated) {
            // get paginated data from database
            $response['data']     = self::createPaginatedResponseData(request(), $model);
        } else {
            $data = $model::all();
            $response['data']     = self::createResponseData($data);
        }

        // $refinedResponse = self::getRefinedData($response);
        // return response()->json($refinedResponse, $status_code);
        return response()->json($response, $status_code);
    }
}
