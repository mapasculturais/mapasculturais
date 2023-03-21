<?php
return [
    /*
    Define filtro do componente `home-opportunities` utilizando a sintaxe da consulta na API

    ex: {"@verified": 1}

    configurando via PHP:
    ex:
    ```
    ["@verified" => 1]
    ```
    */
    'home.opportunities.filter' => json_decode(env('HOME_OPPORTUNITIES_FILTER_JSON', '{"@verified": 1}')), 

    /*
    Define filtro do componente `home-featured` utilizando a sintaxe da consulta na API

    ex: {"@verified": 1}

    configurando via PHP:
    ex:
    ```
    ["@verified" => 1]
    ```
    */
    'home.featured.filter' => json_decode(env('HOME_FEATURED_FILTER_JSON', '{"@verified": 1}'))
];