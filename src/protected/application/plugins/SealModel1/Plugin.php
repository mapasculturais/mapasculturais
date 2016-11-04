<?php

namespace SealModel1;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{

    public function _init(){
        parent::_init();
    }

    function getModelName(){
        return ['label'=> 'Model 1', 'name' => 'seal_model_1'];
    }

}