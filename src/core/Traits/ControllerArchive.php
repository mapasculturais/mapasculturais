<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

/**
 * Trait para controladores que suportam operações de arquivamento de entidades
 * 
 * Este trait fornece métodos para arquivar e desarquivar entidades,
 * permitindo que sejam movidas para um estado de arquivo e restauradas.
 * 
 * @package MapasCulturais\Traits
 */
trait ControllerArchive{
    
    /**
     * Arquiva uma entidade
     * 
     * Este método altera o status da entidade para arquivado.
     * Requer autenticação do usuário.
     * 
     * @api ALL archive
     * @return void Retorna JSON da entidade (AJAX) ou redireciona
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @uses \MapasCulturais\Traits\EntityArchive::archive()
     */
    function ALL_archive(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity_class = $entity->getClassName();

        $entity->archive(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request->getReferer());
        }
    }

    /**
     * Desarquiva uma entidade
     * 
     * Este método restaura uma entidade do estado arquivado.
     * Requer autenticação do usuário.
     * 
     * @api ALL unarchive
     * @return void Retorna JSON da entidade (AJAX) ou redireciona
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @uses \MapasCulturais\Traits\EntityArchive::unarchive()
     */
    function ALL_unarchive(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;
        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $entity_class = $entity->getClassName();

        $entity->unarchive(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            if(in_array($app->request->getReferer(), $urls))
                $app->redirect($app->createUrl('panel'));
            else
                $app->redirect($app->request->getReferer());
        }
    }
}
