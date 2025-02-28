<?php

$fields = [
    'app.registrationCardFields' => [
        "number" => env("SHOW_REGISTRATION_CARD_FIELDS_NUMBER", true),
        "createtimestamp" => env("SHOW_REGISTRATION_CARD_FIELDS_CREATETIMESTAMP", true),
        "owner" => env("SHOW_REGISTRATION_CARD_FIELDS_OWNER", true),
        "category" => env("SHOW_REGISTRATION_CARD_FIELDS_CATEGORY", true),
        "proponentType" => env("SHOW_REGISTRATION_CARD_FIELDS_PROPONENTTYPE", true),
        "coletive" => env("SHOW_REGISTRATION_CARD_FIELDS_COLETIVE", true),
        "status" => env("SHOW_REGISTRATION_CARD_FIELDS_STATUS", true),
        "range" => env("SHOW_REGISTRATION_CARD_FIELDS_RANGE", true),
    ]
];

return $fields;
