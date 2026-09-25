<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * Controlador de Revisões de Entidade
 *
 * Este controlador gerencia as operações relacionadas a revisões de entidades (EntityRevision)
 * no sistema Mapas Culturais. As revisões mantêm um histórico de alterações
 * realizadas nas entidades do sistema, permitindo auditoria e recuperação de versões anteriores.
 *
 * Por padrão, este controlador é registrado com o ID 'entityrevision'.
 *
 * @property-read \MapasCulturais\Entities\EntityRevision $newEntity Nova instância de revisão de entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório de revisões de entidade
 * @property-read array $fields Campos da entidade EntityRevision
 * @property-read \MapasCulturais\Entities\EntityRevision $requestedEntity Revisão de entidade solicitada
 * 
 * @package MapasCulturais\Controllers
 */
class EntityRevision extends EntityController {

    /**
     * Exibe o histórico de revisões de uma entidade
     *
     * Esta ação renderiza a página de histórico de revisões para uma entidade específica.
     * Apenas disponível na versão 1 da API.
     *
     * @api {GET} /entityrevision/history Histórico de revisões
     * @apiDescription Exibe o histórico de revisões de uma entidade
     * @apiGroup ENTITYREVISION
     * @apiName history
     * @apiParam {Number} id ID da entidade
     * @apiVersion 1.0.0
     *
     * @return void
     */
    function GET_history(){
        $app = App::i();

        if($app->view->version >= 2) { 
            $app->pass(); 
        }

        $id = $this->data['id'];

        $entityRevision = $app->repo('EntityRevision')->findCreateRevisionObject($id);

        $this->render('history', ['entityRevision' => $entityRevision]);
    }
}
