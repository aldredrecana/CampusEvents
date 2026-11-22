<?php

return [
    // Leave ALLOWED_EMAIL_DOMAIN empty in .env while developing
    'allowed_email_domain' => env('ALLOWED_EMAIL_DOMAIN'),

    // PUP student number, e.g. 2023-00123-CL-0 (year-number-campus code-digit)
    'student_number_pattern' => '/^\d{4}-\d{5}-[A-Z]{2}-\d$/',
    'student_number_example' => '2023-00123-CL-0',

    // When true, only student numbers in the registrar's masterlist can register
    'require_masterlist' => (bool) env('REQUIRE_MASTERLIST', false),

    // Edit this list to match the programs at your campus
    'courses' => ['BSIT'],

    'checkin_opens_minutes_before' => 30,

    'qr_ttl_seconds' => (int) env('QR_TTL_SECONDS', 45),
    'min_minutes_before_checkout' => 5,
];
