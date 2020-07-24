<?php

return [
    // CEP API
    'cep.endpoint'      => env('CEP_ENDPOINT', 'http://www.cepaberto.com/api/v2/ceps.json?cep=%s'),
    'cep.token_header'  => env('CEP_TOKEN_HEADER', 'Authorization: Token token="%s"'),
    'cep.token'         => env('CEP_TOKEN', ''),
];