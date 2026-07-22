<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * Trait com funções auxiliares para registro de metadados
 * 
 * Este trait fornece métodos convenientes para registrar metadados
 * para diferentes tipos de entidades no sistema Mapas Culturais.
 * 
 * @package MapasCulturais\Traits
 */
trait RegisterFunctions {
    
    /**
     * Registra um metadado para uma classe de entidade
     * 
     * @param string $entity_class Classe da entidade
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerMetadata($entity_class, $key, $cfg) {
        $app = \MapasCulturais\App::i();
        $def = new \MapasCulturais\Definitions\Metadata($key, $cfg);
        return $app->registerMetadata($def, $entity_class);
    }

    /**
     * Registra um metadado para entidades de usuário
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerUserMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\User', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de evento
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerEventMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Event', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de espaço
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerSpaceMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Space', $key, $cfg);
    }

    /**
     * Registra um metadado para configurações de método de avaliação
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerEvauationMethodConfigurationMetadata($key, $cfg) {
        return $this->registerMetadata(Entities\EvaluationMethodConfiguration::class, $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de agente
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerAgentMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Agent', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de projeto
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerProjectMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Project', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de oportunidade
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerOpportunityMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Opportunity', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de inscrição
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerRegistrationMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Registration', $key, $cfg);
    }

    /**
     * Registra um metadado para entidades de selo
     * 
     * @param string $key Chave do metadado
     * @param array $cfg Configuração do metadado
     * @return \MapasCulturais\Definitions\Metadata Definição do metadado registrado
     */
    function registerSealMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Seal', $key, $cfg);
    }
}
