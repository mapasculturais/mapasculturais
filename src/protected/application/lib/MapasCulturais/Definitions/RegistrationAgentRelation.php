<?php
namespace MapasCulturais\Definitions;

/**
 * @property-read boolean $required
 * @property-read string $agentRelationGroupName
 * @property-read string $label
 * @property-read string $description
 * @property-read int $type
 * @property-read array $requiredProperties
 * @property-read array $metadataConfiguration
 * @property-read array $metadataName
 */
class RegistrationAgentRelation extends \MapasCulturais\Definition{

    protected $required = false;

    protected $agentRelationGroupName = '';

    protected $label = '';

    protected $description = '';

    protected $type = null;

    protected $requiredProperties = [];

    function __construct($config) {
        $this->required               = $config['required'];
        $this->agentRelationGroupName = $config['agentRelationGroupName'];
        $this->label                  = $config['label'];
        $this->description            = $config['description'];
        $this->type                   = $config['type'];
        $this->requiredProperties     = $config['requiredProperties'];
    }

    function getMetadataName(){
        return 'useAgentRelation' . ucfirst($this->agentRelationGroupName);
    }

    function getMetadataConfiguration(){
        $app = \MapasCulturais\App::i();
        return [
            'label' => $this->label,
            'type' => 'select',
            'options' => $app->config['registration.agentRelationsOptions']
        ];
    }

    function getOptionLabel($key){
        $cfg = \MapasCulturais\App::i()->config['registration.agentRelationsOptions'];
        if(!$key || !isset($cfg[$key])){
            return '';
        }else{
            return $cfg[$key];
        }
    }
}