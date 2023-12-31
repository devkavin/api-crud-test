<?php

return [
    'student' => [
        'index' => [
            'startDate'  => 'nullable|date',
            // after_or_equal means that the date must be after or equal the given date
            'endDate'    => 'nullable|date|after_or_equal:startDate',
        ],
        'store' => [
            'name'       => 'required',
            'email'      => 'required|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', // can be optimized (common values)
            'phone'      => 'required|numeric|regex:/^0[0-9]{9,11}$/',
            'age'        => 'required|numeric|digits_between:1,3',
            // course must be one of CS SE, CE, ME, MECH
            'course'     => 'required|in:CS SE,CE,ME,MECH'
        ],
        'update' => [
            //not required because it is not required to be changed
            'name'       => 'nullable',
            'email'      => 'nullable|email|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', // email + @ + domain + TLD (Top Level Domain)
            'phone'      => 'nullable|numeric|regex:/^0[0-9]{9,11}$/',
            'age'        => 'nullable|numeric|digits_between:1,3',
            'course'     => 'nullable|in:CS SE,CE,ME,MECH'
        ],
    ],

    'teacher' => [
        'store' => [
            'name'       => 'required',
            'email'      => 'required|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', // can be optimized (common values)
            'phone'      => 'required|numeric|regex:/^0[0-9]{9,11}$/',
            'age'        => 'required|numeric|digits_between:1,3',
            'department' => 'required'
        ],
        'update' => [
            //not required because it is not required to be changed
            'name'       => 'nullable',
            'email'      => 'nullable|email|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', // email + @ + domain + TLD (Top Level Domain)
            'phone'      => 'nullable|numeric|regex:/^0[0-9]{9,11}$/',
            'age'        => 'nullable|numeric|digits_between:1,3',
            'department' => 'nullable'
        ],
    ],
];
