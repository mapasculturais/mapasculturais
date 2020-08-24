<?php

return [
    // CEP API
    'cep.endpoint'      => env('CEP_ENDPOINT', 'https://www.cepaberto.com/api/v3/cep?cep=%s'),

    'cep.token_header'  => env('CEP_TOKEN_HEADER', 'Authorization: Token token="%s"'),
    'cep.token'         => env('CEP_TOKEN', ''),
];