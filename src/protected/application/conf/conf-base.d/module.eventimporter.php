<?php

return [
    'module.EventImporter' => [
        "enabled" => function(){
            $app = MapasCulturais\App::i();
            return $app->user->is("admin");
        },
    ]
];
