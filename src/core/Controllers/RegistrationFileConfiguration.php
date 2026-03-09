<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Controlador para configurações de arquivos de inscrição
 * 
 * Este controlador gerencia as configurações de arquivos para inscrições
 * em oportunidades no sistema Mapas Culturais.
 * 
 * @property-read \MapasCulturais\Entities\RegistrationFileConfiguration $newEntity Nova instância vazia da entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório Doctrine da entidade
 * @property-read array $fields Campos da entidade
 * @property-read \MapasCulturais\Entities\RegistrationFileConfiguration $requestedEntity Entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
class RegistrationFileConfiguration extends EntityController {
    use Traits\ControllerUploads;

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
}
