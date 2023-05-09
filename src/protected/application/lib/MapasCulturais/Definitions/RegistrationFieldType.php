<?php

namespace MapasCulturais\Definitions;

/**
 * This class defines an Registration Field.
 *
 * @property-read array $config
 * @property-read string $slug
 * @property-read string $name
 * @property-read callable $serialize
 * @property-read callable $unserialize
 * @property-read mixed|callable $defaultValue
 * @property-read boolean $requireValuesConfiguration
 * @property-read string $viewTemplate
 * @property-read string $configTemplate
 */
class RegistrationFieldType extends \MapasCulturais\Definition {

    public $_config;
    public $slug;
    public $name;
    public $serialize;
    public $unserialize;
    public $defaultValue;
    public $requireValuesConfiguration = false;
    public $viewTemplate = '';
    public $configTemplate = '';

    public function __construct(array $config) {
        $default = [
            'validations' => []
        ];

        $config = array_merge($default, $config);

        $this->config = $config;

        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }
}
