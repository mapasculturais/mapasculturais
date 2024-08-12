<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Define as rotas de visualização e de edição das entidades
 * 
 * requer que o controller também use o trait `ControllerEntity`
 */
trait ControllerEntityViews {
    
    static function useEntityViews() {
        return true;
    }

    /**
     * Default action.
     *
     * This action renders the template 'index' of this controller.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId');
     * </code>
     *
     */
    function GET_index(){
        $this->render('index');
    }

    /**
     * Renders the single page of the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action renders the template 'single'
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', ['id' => $agent_id])
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', [$agent_id])
     * </code>
     *
     */
    function GET_single() {
        $app = App::i();

        $entity = $this->requestedEntity;;
        
        if (!$entity) {
            $app->pass();
        }

        if ($entity->canUser('view')) {
            if ($app->request->isAjax()) {
                $this->json($entity);
            } else {
                $this->render('single', ['entity' => $entity]);
            }
        } else {
            $app->pass();
        }
    }

    /**
     * Renders the edit form for the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and permission to modify the requested entity.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'edit', ['id' => $agent_id])
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'edit', [$agent_id])
     * </code>
     */
    function GET_edit() {
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }

        $entity->checkPermission('modify');
        if($entity->usesLock()) {
            if($lock = $entity->isLocked()) {
                $current_token = $_COOKIE['lockToken'] ?? null;
    
                if(!($current_token 
                    && $current_token == $lock['token']
                    && $app->user->id == $lock['userId'])   
                ) {
                    unset($lock['token']);
                    $app->view->jsObject['entityLock'] = $lock;
    
                    $app->hook("controller({$this->id}).render(edit)", function(&$template) use($entity) {
                        $template = "locked";
                    });
                } else {
                    $app->view->jsObject['lockToken'] = $current_token;
                }
            } else {
                $lock_token = $entity->lock();
                $app->view->jsObject['lockToken'] = $lock_token;
            }
        }

        if($entity->usesNested()){

            $child_entity_request = $app->repo('RequestChildEntity')->findOneBy(['originType' => $entity->getClassName(), 'originId' => $entity->id]);

            $this->render('edit', ['entity' => $entity, 'child_entity_request' => $child_entity_request]);

        }else{
            $this->render('edit', ['entity' => $entity]);
        }
    }
}