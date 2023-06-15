<?php

namespace MapasCulturais\Definitions;

/**
 * This class defines an Registration Field.
 */
class RegistrationFieldType extends \MapasCulturais\Definition {
    /**
     * Array de configuração do tipo de campo
     * @var array
     */
    public array $config;

    /**
     * Slug do tipo de campo
     * @var string
     */
    public string $slug;

    /**
     * Nome do tipo de campo
     * @var string
     */
    public string $name;

    /**
     * Função de serialização do metadado salvo
     * @var callable
     */
    public $serialize;

    /**
     * Função de deserialização do metadado salvo
     * @var callable
     */
    public $unserialize;

    /**
     * Valor padrão do campo
     * @var mixed
     */
    public $defaultValue;

    /**
     * Indica se o tipo de campo requer configuração de opções, 
     * como em campos de seleção, por exemplo
     * @var bool
     */
    public bool $requireValuesConfiguration = false;

    /**
     * Nome do arquivo de template do tipo de campo no formulário de inscrição
     * @var string
     */
    public string $viewTemplate = '';

    /**
     * Nome do arquivo de template de configuração do tipo de campo
     * @var string
     */
    public string $configTemplate = '';

    /**
     * Validações do tipo de campocampo
     * @var array
     */
    public $validations = [];

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
