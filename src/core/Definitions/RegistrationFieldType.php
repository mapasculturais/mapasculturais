<?php

namespace MapasCulturais\Definitions;

/**
 * Esta classe define um Tipo de Campo de Inscrição
 * 
 * Define as propriedades e comportamentos de um tipo de campo em formulários de inscrição
 * 
 * @property-read array $config Configuração do tipo de campo
 * @property-read string $slug Slug do tipo de campo
 * @property-read string $name Nome do tipo de campo
 * @property-read callable $serialize Função de serialização do metadado
 * @property-read callable $unserialize Função de deserialização do metadado
 * @property-read mixed $defaultValue Valor padrão do campo
 * @property-read bool $requireValuesConfiguration Indica se requer configuração de opções
 * @property-read string $viewTemplate Template do tipo de campo no formulário
 * @property-read string $configTemplate Template de configuração do tipo de campo
 * @property-read array $validations Validações do tipo de campo
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
     * Validações do tipo de campo
     * @var array
     */
    public $validations = [];

    /**
     * Construtor da classe
     * 
     * @param array $config Configuração do tipo de campo
     */
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
