<?php 
use \MapasCulturais\i;

return [
    /* Primeira linha do logo configurável */
    'logo.title' => env('LOGO_TITLE', i::__('Mapas')),

    /* Segunda linha do logo configurável */
    'logo.subtitle' => env('LOGO_SUBTITLE', i::__('Culturais')),

    /* Cores da logo */
    'logo.colors' => [
        "var(--mc-primary-300)",
        "var(--mc-primary-500)",
        "var(--mc-primary-300)",
        "var(--mc-primary-500)",
    ],

    /* 
    Define a url da imagem da logo do site - Substituirá a logo padrão

    ex: `https://mapacultural.com.br/assets/logo.png`
    */
    'logo.image' => env('LOGO_IMAGE', ''),  /* https://www.mapacultural.pe.gov.br/files/subsite/1/file/203/logo_da_plataforma_-_140_x_60_px-01-2-2a890e0a5e4b746f33eff9e5ee07ccf9.jpg */

    /* Esconde o título e subtitulo */
    'logo.hideLabel' => env('LOGO_HIDELABEL', false),
];
