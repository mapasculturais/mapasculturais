<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Controlador para configurações de campos de inscrição
 * 
 * Este controlador gerencia as configurações de campos para inscrições
 * em oportunidades no sistema Mapas Culturais.
 * 
 * @property-read \MapasCulturais\Entities\RegistrationFieldConfiguration $newEntity Nova instância vazia da entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório Doctrine da entidade
 * @property-read array $fields Campos da entidade
 * @property-read \MapasCulturais\Entities\RegistrationFieldConfiguration $requestedEntity Entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
class RegistrationFieldConfiguration extends EntityController {

    /**
     * Redireciona requisições GET para criação
     * 
     * @api GET create
     * @return void Redireciona para a aplicação principal
     */
    function GET_create() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para edição
     * 
     * @api GET edit
     * @return void Redireciona para a aplicação principal
     */
    function GET_edit() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para visualização única
     * 
     * @api GET single
     * @return void Redireciona para a aplicação principal
     */
    function GET_single() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para listagem
     * 
     * @api GET index
     * @return void Redireciona para a aplicação principal
     */
    function GET_index() {
        App::i()->pass();
    }

    /**
     * Valida a configuração de um campo de inscrição sob demanda
     * 
     * @api POST validateConfig
     * @return void Retorna JSON com erros de validação ou true se válido
     */
    function POST_validateConfig() {
        $this->requireAuthentication();

        $entity = $this->requestedEntity;

        // Se não houver entidade requisitada, cria uma nova para validação
        if (!$entity) {
            $entity = new \MapasCulturais\Entities\RegistrationFieldConfiguration();
        }

        // Verifica permissão de modificação (create para novos, modify para existentes)
        if ($entity->id) {
            $entity->checkPermission('modify');
        } else {
            $entity->checkPermission('create');
        }

        // Aplica os dados da requisição na entidade
        foreach ($this->postData as $field => $value) {
            $entity->$field = $value;
        }

        $errors = $entity->getValidationErrors();

        if (!empty($errors)) {
            $this->errorJson($errors);
        } else {
            $this->json(true);
        }
    }
}
