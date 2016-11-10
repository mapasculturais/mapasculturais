<?php

namespace SealModel1;

use SealModelTab;



class Plugin extends SealModelTab\SealModelTemplatePlugin
{
    function getModelName(){
        return ['label'=> 'Modelo 1', 'name' => 'SealModel1'];
    }
}