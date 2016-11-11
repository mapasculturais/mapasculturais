<?php

namespace SealModel2;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelName(){
        return ['label'=> 'Modelo Mapas 2', 'name' => 'SealModel2'];
    }

    function getCssFileName(){
        return 'model-tab-2.css';
    }

    function getBackgroundImage(){
        return 'modelo_certificado_02.jpg';
    }
}