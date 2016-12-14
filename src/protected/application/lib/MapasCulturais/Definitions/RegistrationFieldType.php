<?php

namespace MapasCulturais\Definitions;

/**
 * This class defines an Registration Field.
 *

 */
class RegistrationFieldType extends \MapasCulturais\Definition {

    protected $_config;
    protected $slug;
    protected $name;
    protected $serialize;
    protected $unserialize;
    protected $requireValuesConfiguration = false;

    public function __construct(array $config) {
        $default = [
            'validations' => []
        ];

        $config = array_merge($default, $config);

        $this->_config = $config;

        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }
}
