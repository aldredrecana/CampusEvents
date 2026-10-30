<?php

return [
    // Leave ALLOWED_EMAIL_DOMAIN empty in .env while developing
    'allowed_email_domain' => env('ALLOWED_EMAIL_DOMAIN'),

    // Edit this list to match the programs at your campus
    'courses' => ['BSIT'],

    'checkin_opens_minutes_before' => 30,
];