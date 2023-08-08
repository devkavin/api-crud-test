<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Validator;

class APIHelper
{
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
     * @param null $data
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */

    // MAKE API RESPONSE
    public static function makeAPIResponse($status = true, $message = "Success", $data = null, $status_code = self::HTTP_CODE_SUCCESS)
    {
        $response = [
            "success"     => $status,
            "status_code" => $status_code,
            "message"     => $message,
        ];
        if ($data != null || is_array($data)) {
            // set date one by one for id, name, email, phone, etc (NOT a good practice, just to display timestamp in Y-m-d H:i:s format as requested)
            // reason: makeAPIResponse() formats the response in UTC timezone.
            if (isset($data['id'])) {
                $response['id'] = $data['id'];
            }
            if (isset($data['name'])) {
                $response['name'] = $data['name'];
            }
            if (isset($data['email'])) {
                $response['email'] = $data['email'];
            }
            if (isset($data['phone'])) {
                $response['phone'] = $data['phone'];
            }
            if (isset($data['age'])) {
                $response['age'] = $data['age'];
            }
            if (isset($data['created_at'])) {
                // in Y-m-d H:i:s format
                $response['created_at'] = $data['created_at']->format('Y-m-d H:i:s');
            }
            if (isset($data['updated_at'])) {
                // in Y-m-d H:i:s format
                $response['updated_at'] = $data['updated_at']->format('Y-m-d H:i:s');
            }
        }
        // proper response format
        if ($data != null || is_array($data)) {
            $response["data"] = $data;
        }

        // return response
        return response()->json($response, $status_code);
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
    public static function validateRequest($schema, $request, $type = 'insert')
    {
        // Get schema keys into a array
        $schema_keys = array_keys($schema);

        // If the request is not and create, $request will take passed data
        $input = $request;

        // Only get full request object when creating
        // Ignore when doing the update
        if ($type == 'insert') {
            // Remove unnecessary fields from request
            $input = $request->only($schema_keys);
        }

        // Validate data feilds against schema
        $validator = Validator::make($input, $schema);

        // Return validation errors, if something went wrong
        if ($validator->fails()) {
            return ['errors' => true, 'error_messages' => $validator->errors()];
        }

        return ['errors' => false, 'data' => $input];
    }
}
