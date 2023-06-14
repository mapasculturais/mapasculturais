<?php

namespace SealModel1;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelData(){
        return [
            'label'=> 'Modelo Mapas 1',
            'name' => 'SealModel1',
            'css' => 'model-tab-1.css',
            'background' => 'modelo_certificado_01.jpg',
            'preview' => 'preview-model1.png'
        ];
    }
}