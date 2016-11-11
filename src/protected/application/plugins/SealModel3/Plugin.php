<?php

namespace SealModel3;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelName(){
        return ['label'=> 'Modelo Mapas 3', 'name' => 'SealModel3'];
    }

    function getCssFileName(){
        return 'model-tab-3.css';
    }

    function getBackgroundImage(){
        return 'modelo_certificado_03.jpg';
    }
}