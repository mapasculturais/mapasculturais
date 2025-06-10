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

        $address_metadata = [
            'address_postal_code' => i::__('Código postal'),
            'address_level_1'     => i::__('Campo de endereço de nível 1 (Região)'),
            'address_level_2'     => i::__('Campo de endereço de nível 2 (Estado/Província)'),
            'address_level_3'     => i::__('Campo de endereço de nível 3 (Departamento)'),
            'address_level_4'     => i::__('Campo de endereço de nível 4 (Cidade/Município/Comune)'),
            'address_level_5'     => i::__('Campo de endereço de nível 5 (Subprefeitura/Distrito)'),
            'address_level_6'     => i::__('Campo de endereço de nível 6 (Bairro)'),
            'address_line_1'      => i::__('Endereço linha 1'),
            'address_line_2'      => i::__('Endereço linha 2')
        ];

        foreach ($address_metadata as $slug => $label) {
            $this->registerAgentMetadata($slug, ['label' => $label]);
            $this->registerSpaceMetadata($slug, ['label' => $label]);
        }

        $app->registerCountryLocalization(new BrasilLocalization());
    }
}