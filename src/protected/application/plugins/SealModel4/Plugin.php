<?php

namespace SealModel4;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelData(){
        return [
            'label'=> 'Modelo Mapas 4',
            'name' => 'SealModel4',
            'css' => 'model-tab-4.css',
            'background' => 'modelo_certificado_04.jpg',
            'preview' => 'preview-model4.png'
        ];
    }
}