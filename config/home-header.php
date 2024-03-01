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

    /* Link vinculado ao primeiro banner */
    'homeHeader.bannerLink' => env('HOME_HEADER_LINK', ''),
    
    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.downloadableLink' => env('HOME_HEADER_DOWNLOADABLE', false),

    'homeHeader.secondBanner' => env('HOME_HEADER_BANNER', ''),
    
    /* Link vinculado ao segundo banner */
    'homeHeader.secondBannerLink' => env('HOME_HEADER_LINK', ''),
       
    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.secondDownloadableLink' => env('HOME_HEADER_SECOND_DOWNLOADABLE', false),

    /* 
    Define o nome do asset do terceiro banner do header da homepage 
    ex: `img/meu-terceiro-banner.jpg` (pasta assets/img/meu-terceiro-banner.jpg do tema)
    */
    'homeHeader.thirdBanner' => env('HOME_HEADER_THIRD_BANNER', ''),

    /* Link vinculado ao  terceiro banner */
    'homeHeader.thirdBannerLink' => env('HOME_HEADER_THIRD_LINK', ''),

    /* Define se link é para download ou para abrir em uma nova aba */
    'homeHeader.thirdDownloadableLink' => env('HOME_HEADER_THIRD_DOWNLOADABLE', false),
];
