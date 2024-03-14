<?php
namespace MapasCulturais\Traits;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Exceptions\WorkflowRequest;

/**
 * Define as rotas POST, PUT, PATCH e DELETE para as entidades.
 * 
 * requer que o controller também use o trait `ControllerEntity`
 */
trait ControllerEntityActions {

    static function useEntityActions() {
        return true;
    }


    function setEntityProperties(Entity $entity, array $data) {
        unset($data['id']);

        $metadata = $entity->propertiesMetadata;
        
        foreach($data as $property => $value) {
            if($type = $metadata[$property]['type'] ?? false) {
                if(in_array($type, ['bool', 'boolean'])) {
                    if($value === 'false') {
                        $value = false;
                    } else {
                        $value = (bool) $value;
                    }
                } else if(in_array($type, ['int', 'integer', 'smallint'])) {
                    $value = (int) $value;
                } else if(in_array($type, ['numeric', 'float', 'number'])) {
                    $value = (float) $value;
                }
            }
            
            $current_value = $entity->$property;

            if($current_value !== $value) {
                $entity->$property = $value;
            }
        }
    }

    public function ALL_enqueuePCache() {
        $app = App::i();
        
        $this->requireAuthentication();

        $entity = $this->requestedEntity;

        if(!$entity) {
            $app->pass();
        }

        $entity->checkPermission('@control');

        $users = [];
        if ($users_data = $this->data['users'] ?? '') {
            if($users_data == 'each') {
                $users = $entity->getExtraPermissionCacheUsers();
            } else {
                $users_data = explode(',', $users_data);
                $users = $app->repo('User')->findBy(['id' => $users_data]);
            }
        }

        $entity->enqueueToPCacheRecreation($users);
    }

    /**
     * 
     * @apiDefine APICreate
     * @apiDescription Cria uma nova entidade
     * @apiParam {Array} [data] Array com valores para popular os atributos da entidade. Use o método describe para descobrir os atributos.
     */ 
     
     /**
     * Creates a new entity of the class with same name in the parent\Entities namespace
     *
     * This action requires authentication and outputs the json with the new entity or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId');
     * </code>
     */
    function POST_index($data = null) {
        $this->requireAuthentication();

        if (is_null($data)) {
            $data = $this->postData;
        }

        $entity = $this->getRequestedEntity();

        $app = App::i();
        $app->applyHookBoundTo($this, "POST({$this->id}.index):data", ['data' => &$data]);

        foreach($data as $field=>$value){
            $entity->$field = $value;
        }

        if($errors = $entity->validationErrors){
            if ($entity->getEntityType() == 'Opportunity' && in_array("term-area", array_keys($errors)) && $errors['term-area']) {
                foreach($errors['term-area'] as &$termError) {
                    if(strpos($termError, i::__('área de atuação')) !== false) {
                        $termError = str_replace(i::__('área de atuação'), i::__('área de interesse'), $termError);
                    }
                }
            }
            $this->errorJson($errors);
        }else{
            $this->_finishRequest($entity, true);
        }
    }

    /**
     * Alias to PUT_single
     * @see self::PUT_single
     */
    function POST_single(){
        $this->PUT_single();
    }

    /**
     * 
     * @apiDefine APIPut
     * @apiDescription Atualiza completamente uma entidade.
     * @apiParam {Array} [data] Array com valores para popular os atributos da entidade. Use o método describe para descobrir os atributos. Atenção: Todos os dados da entidade devem estar na requisição, inclusive aqueles que não serão alterados.
     */

     /** 
     * Updates the entity with the id specified in the URL with the values sent by PUT.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and perission to modify the requested entity.
     *
     * This action outputs a json with the entity data or with an array of errors.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', ['id' => $agent_id])
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', [$agent_id])
     * </code>
     */
    function PUT_single($data = null) {
        $this->requireAuthentication();

        if (is_null($data)) {
            $data = $this->postData;
        }

        $app = App::i();

        $app->applyHookBoundTo($this, "PUT({$this->id}.single):data", ['data' => &$data]);

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $function = null;

        if(isset($data['status']) && isset(self::$changeStatusMap[$entity->status][(int)$data['status']])) {
            $function = self::$changeStatusMap[$entity->status][(int)$data['status']];
            unset($data['status']);
        }

        $this->setEntityProperties($entity, $data);

        if($errors = $entity->validationErrors){
            $this->errorJson($errors);
        }else{
            $this->_finishRequest($entity, true, $function);
        }
    }


    /**
     * 
     * @apiDefine APIPatch
     * @apiDescription Atualiza parcialmente uma entidade.
     * @apiParam {Array} [data] Array com valores para popular os atributos da entidade. Use o método describe para descobrir os atributos. 
     */
    function PATCH_single($data = null) {
        $this->requireAuthentication();

        if (is_null($data)) {
            $data = $this->postData;
        }

        $app = App::i();

        $app->applyHookBoundTo($this, "PATCH({$this->id}.single):data", ['data' => &$data]);

        $entity = $this->requestedEntity;

        if($entity->usesPermissionCache() && $entity->usesOwnerAgent() && (!isset($data['ownerId']) && !isset($data['owner']) && !isset($data['status']))) {
            $entity->__skipQueuingPCacheRecreation = true;
            if ($entity instanceof \MapasCulturais\Entities\Registration) {
                $entity->owner->__skipQueuingPCacheRecreation = true;
            }
        }

        if(!$entity)
            $app->pass();
        
        $function = null;

        $this->setEntityProperties($entity, $data);

        if($_errors = $entity->validationErrors){
            $errors = [];
            $has_terms_in_request = [];
            foreach($this->postData as $field => $value){
                if ($error = $_errors[$field] ?? false){
                    $errors[$field] = $error;
                } else if ($field == 'terms') {
                    $has_terms_in_request = true;
                }
            }

            if ($has_terms_in_request) {
                foreach ($entity->validationErrors as $key => $list_of_errors) {
                    if (str_starts_with($key, 'term-')) {
                        $errors[$key] = $list_of_errors;
                    }
                }
            }

            if($errors) {
                $this->errorJson($errors);
            }
        }

        $this->_finishRequest($entity, true, $function);
    }

    /**
     * Validates $data for $entity
     *
     * @param Entity $entity
     * @param array $data
     * @return array validation errors
     */
    function validate(Entity $entity, array $data) {
        foreach ($data as $field => $value) {
            $entity->$field = $value;
        }
        $errors = $entity->validationErrors;
        return $errors;
    }

    /**
     * Validates properties for entity
     *
     * @return void
     */
    function POST_validateProperties() {
        $entity = $this->requestedEntity;

        if (!$entity) {
            App::i()->pass();
        }

        $entity->checkPermission('validate');
        
        if ($_errors = $this->validate($entity, $this->postData)) {
            $errors = [];
            foreach($this->postData as $field => $value){
                if(key_exists($field, $_errors)){
                    $errors[$field] = $_errors[$field];
                }
            }

            if($errors){
                $this->errorJson($errors);
            }
        } 
        
        $this->json(true);
    }

    /**
     * Validates data for entity
     */
    function POST_validateEntity() {
        $entity = $this->requestedEntity;

        if (!$entity) {
            App::i()->pass();
        }

        $entity->checkPermission('validate');

        if ($errors = $this->validate($entity, $this->postData)) {
            $this->errorJson($errors);
        } else {
            $this->json(true);
        }
    }

    /**
     * Alias to DELETE_single
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'delete', ['id' => $agent_id])
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'delete', [$agent_id])
     * </code>
     */
    function GET_delete(){
        $this->DELETE_single();
    }

    /**
     * 
     * @apiDefine APIDelete
     * @apiDescription Deleta uma entidade.
     */
    
    /**
     * Delete the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and permission to delete the requested entity.
     *
     * If the request is an ajax request, outputs an json with value true, otherwise redirects back to referer.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', ['id' => $agent_id])
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', [$agent_id])
     * </code>
     */
    function DELETE_single(){
        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $single_url = $entity->singleUrl;

        $entity->delete(true);

        if($this->isAjax()){
            $this->json($entity->simplify('id,name,type,status'));
        }else{
            //e redireciona de volta para o referer
            $redirect_url = $app->request->getReferer();
            if($redirect_url === $single_url)
                $redirect_url = $app->createUrl ('panel');

            $app->applyHookBoundTo($this, "DELETE({$this->id}):beforeRedirect", [$entity, &$redirect_url]);

            $app->redirect($redirect_url);
        }
    }


    protected function _finishRequest($entity, $isAjax = false, $function = null){
        $status = 200;
        try{
            if($function){
                $entity->$function(true);
            } else {
                $entity->save(true);
            }
        }  catch (WorkflowRequest $e){
            $status = 202;
            $reqs = [];
            foreach($e->requests as $request){
                $reqs[] = $request->getRequestType();
            }

            header('CreatedRequests: ' . json_encode($reqs));
        }
        
        $this->finish($entity, $status, $isAjax);
    }

    function finish($data, $status = 200, $isAjax = false){
        $app = App::i();
        $app->applyHookBoundTo($this, 'request.finish', [$data, $status]);
        if(isset($this->getData['redirectTo'])){
            $app->redirect($this->getData['redirectTo'], $status);
        }elseif($this->isAjax() || $isAjax || $app->request->getHeaderLine('MapasSDK-REQUEST')){
            $this->json($data, $status);
        }else{
            $app->redirect($app->request->getReferer(), $status);
        }
    }
}