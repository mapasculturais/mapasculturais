<?php 
use \MapasCulturais\i;

return [
    /* 
    Define o nome do asset da imagem do background e banner no header da home - Substituirá o background padrão
    ex: `img/meu-home-header-background.jpg` (pasta assets/img/meu-home-header-background.jpg do tema)
    */
    'homeHeader.background' => env('HOME_HEADER_BACKGROUND', ''),

    /* 
    Define o nome do asset do banner do header da homepage 
    ex: `img/meu-banner.jpg` (pasta assets/img/meu-banner.jpg do tema)
    */
    'homeHeader.banner' => env('HOME_HEADER_BANNER', ''),
    /* Link vinculado ao banner */
    'homeHeader.bannerLink' => env('HOME_HEADER_LINK', ''),
    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.downloadableLink' => env('HOME_HEADER_DOWNLOADABLE', false),

    /* 
    Define o nome do asset do segundo banner do header da homepage 
    ex: `img/meu-segundo-banner.jpg` (pasta assets/img/meu-segundo-banner.jpg do tema)
    */
    'homeHeader.secondBanner' => env('HOME_HEADER_BANNER', ''),
    /* Link vinculado ao banner */
    'homeHeader.secondBannerLink' => env('HOME_HEADER_LINK', ''),
    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.secondDownloadableLink' => env('HOME_HEADER_SECOND_DOWNLOADABLE', false),
];
