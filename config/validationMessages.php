<?php

return [
    "test_response"           => "This is a Test response",

    "required"                => "This field is required",

    "filled"                  => "This field must have a value",

    "invalid_data"            => "Invalid data",
    "invalid_request"         => "Invalid request",

    "success"   => [
        "store"               => "Data stored successfully",
        "update"              => "Data updated successfully",
        "delete"              => "Data deleted successfully",

        "action"               => "Action completed successfully",
    ],

    "failed"    => [
        "store"               => "Data failed to store",
        "update"              => "Data failed to update",
        "delete"              => "Data failed to delete",

        "action"               => "Action failed",
    ],

    "invalid"   => [
        "store"               => "Invalid data to store",
        "update"              => "Invalid data to update",
        "delete"              => "Invalid data to delete",

        "action"               => "Invalid action",
    ],

    "not_found" => [
        "store"               => "Data not found to store",
        "update"              => "Data not found to update",
        "retrieve"            => "Data not found to retrieve",
        "delete"              => "Data not found to delete",
    ],

    "exist"     => [
        "store"               => "Data already exist, nothing to store",
        "update"              => "Data already exist, nothing to update",
    ],
];
