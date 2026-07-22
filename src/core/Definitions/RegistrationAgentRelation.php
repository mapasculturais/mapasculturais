<?php
namespace MapasCulturais\Definitions;

/**
 * Esta classe define uma Relação de Agente em Inscrições
 * 
 * @property-read boolean $required Indica se a relação é obrigatória
 * @property-read string $agentRelationGroupName Nome do grupo de relação de agente
 * @property-read string $label Rótulo da relação
 * @property-read string $description Descrição da relação
 * @property-read int $type Tipo da relação
 * @property-read array $metadataConfiguration Configuração de metadados
 * @property-read array $metadataName Nome do metadado
 */
class RegistrationAgentRelation extends \MapasCulturais\Definition{

    /**
     * Indica se a relação é obrigatória
     * @var bool
     */
    public $required = false;

    /**
     * Nome do grupo de relação de agente
     * @var string
     */
    public $agentRelationGroupName = '';

    /**
     * Rótulo da relação
     * @var string
     */
    public $label = '';

    /**
     * Descrição da relação
     * @var string
     */
    public $description = '';

    /**
     * Tipo da relação
     * @var int|null
     */
    public $type = null;

    /**
     * Construtor da classe
     * 
     * @param array $config Configuração da relação
     */
    function __construct($config) {
        $this->required               = $config['required'];
        $this->agentRelationGroupName = $config['agentRelationGroupName'];
        $this->label                  = $config['label'];
        $this->description            = $config['description'];
        $this->type                   = $config['type'];
    }

    /**
     * Obtém o nome do metadado
     * 
     * @return string
     */
    function getMetadataName(){
        return 'useAgentRelation' . ucfirst($this->agentRelationGroupName);
    }

    /**
     * Obtém a configuração de metadados
     * 
     * @return array
     */
    function getMetadataConfiguration(){
        $app = \MapasCulturais\App::i();
        return [
            'label' => $this->label,
            'type' => 'select',
            'options' => $app->config['registration.agentRelationsOptions']
        ];
    }

    /**
     * Obtém o rótulo da opção
     * 
     * @param string $key Chave da opção
     * @return string
     */
    function getOptionLabel($key){
        $cfg = \MapasCulturais\App::i()->config['registration.agentRelationsOptions'];
        if(!$key || !isset($cfg[$key])){
            return '';
        }else{
            return $cfg[$key];
        }
    }
}