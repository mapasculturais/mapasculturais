<?php
/*
 * Imagens padrão de compartilhamento (Open Graph / Twitter).
 * Com ThemeCustomizer ativo, um arquivo enviado no painel Aparência (grupo share do subsite) substitui estes valores por URL do arquivo naquele subsite.
 */

return[
    'share.image' => env('SHARE_IMAGE', 'img/share.png'),
    'share.image_twitter' =>  env('SHARE_IMAGE_TWITTER', 'img/share.png'),
];