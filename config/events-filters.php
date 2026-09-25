<?php

return [
    // Exibe o filtro de Estado e Cidade na tela de busca de eventos.
    // Útil principalmente em instalações nacionais (padrão: false).
    'events.filter.statesAndCities' => env('EVENTS_FILTER_STATES_AND_CITIES', false),

    // Exibe o filtro de Selos na tela de busca de eventos (padrão: false).
    'events.filter.seals' => env('EVENTS_FILTER_SEALS', false),
];
