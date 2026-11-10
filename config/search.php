<?php
return [
    'search.events.to' => env('SEARCH_EVENTS_TO', '+30 days'),

    // Recorte territorial das buscas públicas (ADR 0019 — docs/reference/decisions/0019).
    // Lista de estados pelos nomes exibidos ao usuário, separados por vírgula
    // (ex.: "São Paulo, Rio de Janeiro"). A comparação ignora acentos e caixa.
    // Quando preenchido, os resultados das buscas ficam restritos a esses estados
    // e o filtro de estado é ocultado do usuário.
    'search.filters.states' => env('SEARCH_FILTER_STATES', ''),

    // Lista de municípios pelos nomes exibidos ao usuário, separados por vírgula
    // (ex.: "São Paulo, Campinas"). Só tem efeito se 'search.filters.states'
    // estiver preenchido; municípios fora dos estados configurados são ignorados.
    'search.filters.cities' => env('SEARCH_FILTER_CITIES', ''),
];