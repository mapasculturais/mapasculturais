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

        $app->hook('entity(<<Agent|Space>>).save:before', function() use($app) {
            /** @var \MapasCulturais\Entities\Agent|\MapasCulturais\Entities\Space $this */
            $country = $this->address_level0;

            if($country && ($country_localization = $app->getRegisteredCountryLocalizationByCountryCode($country))) {
                for($i = 0; $i <= 6; $i++) {
                    $metadata = "address_level{$i}";
                    $getter = "getLevel{$i}";

                    $this->$metadata = $country_localization->$getter($this);
                }

                $this->address_postalCode = $country_localization->getPostalCode($this);
                $this->address_line1 = $country_localization->getLine1($this);
                $this->address_line2 = $country_localization->getLine2($this);
                $this->address = $country_localization->getFullAddress($this);
                $country_localization->setLevel0($this, $country);
            } 
        });
    }

    function register(){
        $app = App::i();
        
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['country-localization'])) {
            $app->registerController('country-localization', Controller::class);
        }

        $address_metadata = [
            'address'            => i::__('Endereço completo'),
            'address_postalCode' => i::__('Código postal'),
            'address_level0'     => i::__('País'),
            'address_line1'      => i::__('Endereço linha 1'),
            'address_line2'      => i::__('Endereço linha 2')
        ];

        $level_labels = $app->config['address.defaultLevelsLabels'];

        for($i = 1; $i <= 6; $i++) {
            $label_config = $level_labels[$i];
            $address_metadata["address_level{$i}"] = $label_config;
        }

        foreach ($address_metadata as $slug => $label) {
            $this->registerAgentMetadata($slug, [
                'label' => $label,
                'private' => function(){
                    return !$this->publicLocation;
                },
                'serialize' => function($value, $entity) use($slug, $app) {
                    $country = $slug == 'address_level0' ? $value : $entity->address_level0;
                    if($slug == 'address') {
                        $slug = 'address_fullAddress';
                    }

                    if($country && $country_localization = $app->getRegisteredCountryLocalizationByCountryCode($country)) {
                        $setter = 'set'.substr($slug, 8);

                        $country_localization->$setter($entity, $value);
                    }

                    return $value;
                },
            ]);
            $this->registerSpaceMetadata($slug, ['label' => $label]);
        }

        $app->registerCountryLocalization(new BrasilLocalization());
    }
}