<?php

namespace CompliantSuggestion;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Module extends \MapasCulturais\Module{

    public function __construct(array $config = array()) {
        $config = $config + ['compliant' => true, 'suggestion' => true];
        
        parent::__construct($config);
    }

    public function _init() {
        $app = App::i();

        $plugin = $this;

        $params = [];

        if(array_key_exists('compliant',$this->_config)) {
            $params['compliant'] = $this->_config['compliant'];
        }

        if(array_key_exists('suggestion',$this->_config)) {
            $params['suggestion'] = $this->_config['suggestion'];
        }

        $app->hook('template(<<agent|space|event|project>>.<<single>>.main-content):end', function() use ($app, $plugin, $params) {
            $this->part('compliant_suggestion.php',$params);
        });
    }

    public function register() { }
}
