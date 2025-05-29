<?php

namespace CountryLocalizations;

use MapasCulturais\App;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    function _init(){
        /** @var App $app */
        $app = App::i();
    }

    function register(){
        $app = App::i();
        
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['country-localization'])) {
            $app->registerController('country-localization', Controller::class);
        }

        $levels = [
            'address_level_1' => i::__('Campo de endereço de nível 1'),
            'address_level_2' => i::__('Campo de endereço de nível 2'),
            'address_level_3' => i::__('Campo de endereço de nível 3'),
            'address_level_4' => i::__('Campo de endereço de nível 4'),
            'address_level_5' => i::__('Campo de endereço de nível 5'),
            'address_level_6' => i::__('Campo de endereço de nível 6'),
        ];

        foreach ($levels as $slug => $label) {
            $this->registerAgentMetadata($slug, ['label' => $label]);
            $this->registerSpaceMetadata($slug, ['label' => $label]);
        }
    }
}