<?php

return [
    "test_response"            => "This is a Test response",
    "required"                 => "This field is required",
    "filled"                   => "This field must have a value",

    "success"   => [
        "success"              => "Success",
        "store"                => "Data stored successfully",
        "update"               => "Data updated successfully",
        "delete"               => "Data deleted successfully",
        "action"               => "Action completed successfully",
        "restore"              => "Data restored successfully",
        "force_delete"         => "Data deleted permanently",
    ],

    "failed"    => [
        "delete"               => "Data failed to delete",
        "store"                => "Data failed to store",
        "update"               => "Data failed to update",
        "action"               => "Action failed",
        "restore"              => "Data failed to restore",
        "force_delete"         => "Data failed to delete permanently",
    ],

    "invalid"   => [
        "store"                => "Invalid data to store",
        "update"               => "Invalid data to update",
        "delete"               => "Invalid data to delete",
        "action"               => "Invalid action",
        "email"                => "Invalid email address",
        "phone"                => "Invalid phone number",
        "age"                  => "Invalid age",
        "date"                 => "Invalid date",
        "numeric"              => "Invalid number",
        "regex"                => "Invalid format",
        "startDate"            => "Invalid start date",
        "endDate"              => "Invalid end date",
        "page"                 => "Invalid page number",
        "limit"                => "Invalid limit",
    ],

    "not_found" => [
        "store"                => "Data not found to store",
        "update"               => "Data not found to update",
        "retrieve"             => "Data not found to retrieve",
        "delete"               => "Data not found to delete",
        "student"              => "Student not found",
        "teacher"              => "Teacher not found",
        "department"           => "Department not found",
        "course"               => "Course not found",
        "page"                 => "Page not found",
        "image"                => "Image not found",
    ],

    "exist"     => [
        "store"                => "Data already exist, nothing to store",
        "update"               => "Data already exist, nothing to update",
    ],

    "regex"     => [
        "name"                 => "Invalid name",
        "email"                => "Invalid email address",
        "phone"                => "Invalid phone number",
        "age"                  => "Invalid age",
        'image'                => "Invalid image",

    ],

    "image"     => [
        "required"             => "Image is required",
        "invalid"              => "Invalid image",
        "extension"            => "Invalid image extension",
        "size"                 => "Image size is too large",
    ],
];
