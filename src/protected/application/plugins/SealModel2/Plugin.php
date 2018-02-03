<?php

namespace SealModel2;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelData(){
        return [
            'label' => 'Modelo Mapas 2',
            'name' => 'SealModel2',
            'css' => 'model-tab-2.css',
            'background' => 'modelo_certificado_02.jpg',
            'preview' => 'preview-model2.png'
        ];
    }
}