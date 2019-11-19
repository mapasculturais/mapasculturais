<?php

namespace SealModel3;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelData(){
        return [
            'label'=> 'Modelo Mapas 3',
            'name' => 'SealModel3',
            'css' =>  'model-tab-3.css',
            'background' => 'modelo_certificado_03.jpg',
            'preview' => 'preview-model3.png'
        ];
    }
}