<?php
if(($_SERVER['HTTP_HOST'] ?? "localhost") == "v1.localhost" ){
    return [];
};

if(!function_exists("__switch_theme")){
    function __switch_theme() {
        if(!preg_match('#/(autenticacao|auth|site)#',$_SERVER['REQUEST_URI'] ?? '/')) {
            return 'MapasCulturais\\Themes\\BaseV2';
        } else {
            return 'MapasCulturais\\Themes\\BaseV1';
        }
    }
}


return [
    'themes.active' => __switch_theme()
];