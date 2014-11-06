<?php
namespace MapasCulturais\Definitions;

/**
 * @property-read boolean $required
 * @property-read string $agentRelationGroupName
 * @property-read string $label
 * @property-read string $description
 * @property-read int $type
 * @property-read array $requiredProperties
 */
class RegistrationAgentRelation extends \MapasCulturais\Definition{
   
    protected $required = false;
    
    protected $agentRelationGroupName = '';
    
    protected $label = '';
    
    protected $description = '';
    
    protected $type = null;
    
    protected $requiredProperties = array();
    
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
        return array(
            'label' => sprintf($app->txt('Use relation %s'), $this->label),
            'type' => 'select',
            'options' => array(
                $app->txt('Required') => $app->txt('Required'),
                $app->txt('Facultative') => $app->txt('Facultative'),
                $app->txt("Don't use") => $app->txt("Don't use")
            )
        );
    }
}