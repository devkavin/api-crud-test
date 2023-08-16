<?php

namespace App\Constants;

class Constants
{

    const SEARCH = [
        'name'        => 'name',
        'email'       => 'email',
        'phone'       => 'phone',
        'age'         => 'age',

        'created_at'  => 'created_at',
        'updated_at'  => 'updated_at',
    ];

    const PAGINATION = [
        'page'        => 'page',
        'limit'       => 'limit',
        'offset'      => 'offset',
    ];

    const RESPONSE = [
        'status'      => 'status',
        'message'     => 'message',
        'data'        => 'data',
        'total'       => 'total',
        'page'        => 'page',
        'limit'       => 'limit',
    ];
}
