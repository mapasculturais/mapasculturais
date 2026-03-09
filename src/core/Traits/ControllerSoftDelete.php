<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

/**
 * Trait para controladores que suportam exclusão lógica (soft delete) de entidades
 * 
 * Este trait fornece métodos para restaurar entidades excluídas logicamente
 * e para destruir permanentemente entidades (hard delete).
 * 
 * @package MapasCulturais\Traits
 */
trait ControllerSoftDelete{
    
    /**
     * Restaura uma entidade excluída logicamente
     * 
     * Este método altera o status da entidade de excluída para ativa.
     * Requer autenticação do usuário.
     * 
     * @api ALL undelete
     * @return void Retorna JSON da entidade (AJAX) ou redireciona
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @uses \MapasCulturais\Traits\EntitySoftDelete::undelete()
     */
    function ALL_undelete(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity->undelete(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request->getReferer());
        }
    }

    /**
     * Destrói permanentemente uma entidade
     * 
     * Este método realiza uma exclusão física (hard delete) da entidade.
     * Requer autenticação do usuário.
     * 
     * @api ALL destroy
     * @return void Retorna JSON simplificado da entidade (AJAX) ou redireciona
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @uses \MapasCulturais\Traits\EntitySoftDelete::destroy()
     */
    function ALL_destroy(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $entity->destroy(true);

        if($this->isAjax()){
            $this->json($entity->simplify('id,name'));
        }else{
            //e redireciona de volta para o referer
            if(in_array($app->request->getReferer(), $urls))
                $app->redirect($app->createUrl('panel'));
            else
                $app->redirect($app->request->getReferer());
        }
    }
}