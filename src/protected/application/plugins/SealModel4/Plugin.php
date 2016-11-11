<?php

namespace SealModel4;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelName(){
        return ['label'=> 'Modelo Mapas 4', 'name' => 'SealModel4'];
    }

    function getCssFileName(){
        return 'model-tab-4.css';
    }

    function getBackgroundImage(){
        return 'modelo_certificado_04.jpg';
    }
}