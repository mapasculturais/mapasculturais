<?php 
use \MapasCulturais\i;

return [
    /* Define o título do header da homepage */
    'homeHeader.title' => env('HOME_HEADER_TITLE', i::__('Bem-vinde ao Mapas Culturais')),

    /* Define a descrição do header da homepage */
    'homeHeader.description' => env('HOME_HEADER_DESCRIPTION', i::__('O Mapas Culturais é uma ferramenta de gestão cultural, que garante a estruturação de Sistemas de Informações e Indicadores. A plataforma oferece soluções para o mapeamento colaborativo de agentes culturais, realização de todas as etapas de editais e fomentos, organização de uma agenda cultural e divulgação espaços culturais dos territórios.')),

    /* 
    Define o nome do asset da imagem do background e banner no header da home - Substituirá o background padrão
    ex: `img/meu-home-header-background.jpg` (pasta assets/img/meu-home-header-background.jpg do tema)
    */
    'homeHeader.background' => env('HOME_HEADER_BACKGROUND', ''),

    /* 
    Define o banner do header na homepage 
    ex: `img/meu-banner.jpg` (pasta assets/img/meu-banner.jpg do tema)
    */
    'homeHeader.banner' => env('HOME_HEADER_BANNER', ''),
    /* Link vinculado ao banner */
    'homeHeader.bannerLink' => env('HOME_HEADER_LINK', ''),
    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.downloadableLink' => env('HOME_HEADER_DOWNLOADABLE', false),
];
