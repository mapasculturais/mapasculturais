<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Exceptions\WorkflowRequest;

/**
 * This is the base class to Entity Controllers
 *
 * @property-read \MapasCulturais\Entity $newEntity An empty new entity object of the class related to this controller
 * @property-read \Doctrine\ORM\EntityRepository $repository the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
 * @property-read array $fields the fields of the entity with the same name of the controller in the same parent namespace.
 * @property-read \MapasCulturais\Entity $requestedEntity The requested Entity
 */
abstract class EntityController extends \MapasCulturais\Controller{


    /**
     * The class name of the entity with the same name of the controller in the same parent namespace.
     *
     * @example for the controller \MapasCulturais\Controllers\User the value will be \MapasCulturais\Entities\User
     * @example for the controller \MyPlugin\Controllers\User the value will be \MyPlugin\Entities\User
     *
     * @var string the entity class name
     */
    protected $entityClassName;

    
    protected $_requestedEntity = false;


    /**
     * The controllers constructor.
     *
     * This method sets the controller entity class name with an class with the same name of the controller in the parent namespace.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     */
    protected function __construct() {
        $this->entityClassName = preg_replace("#Controllers\\\([^\\\]+)$#", 'Entities\\\$1', get_class($this));
    }


    /**
     * Is this an AJAX request?
     *
     * @return bool
     */
    public function isAjax(){
        return App::i()->request->isAjax();
    }


    /**
     * Creates and returns an empty new entity object of the entity class related with this controller.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     *
     * @return \MapasCulturais\entityClassName An empty new entity object.
     */
    public function getNewEntity(){
        $class = $this->entityClassName;
        return new $class;
    }


    /**
     * Returns the etity with the requested id.
     *
     * @example for the url http://mapasculturais/agent/33  or http://mapasculturais/agent/id:33 returns the agent with the id 33
     *
     * @return \MapasCulturais\Entity|null
     */
    public function getRequestedEntity(){
        if ($this->_requestedEntity !== false) {
            return $this->_requestedEntity;
        }
        
        if (key_exists('id', $this->urlData)) {
            $this->_requestedEntity = $this->repository->find($this->urlData['id']);
        } elseif ($this->action === 'create' || ($this->method == 'POST' && $this->action === 'index')) {
            $this->_requestedEntity = $this->newEntity;
        } else {
            $this->_requestedEntity = null;
        }
        
        return $this->_requestedEntity;
    }

    /**
     * Returns the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::repo()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(){
        return App::i()->repo($this->entityClassName);
    }



    /**
     * Alias to getRepository
     *
     * @see \MapasCulturais\Controller::getRepository()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return $this->getRepository();
    }


    /**
     * Returns the fields of the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::fields()
     *
     * @return array of fields
     */
    public function getFields(){
        return App::i()->fields($this->entityClassName);
    }

    /**
     * Alias to getFields()
     *
     * @see \MapasCulturais\Entities\EntityController::getFields()
     *
     * @return array of fields
     */
    public function fields(){
        return $this->getFields();
    }

    protected function _finishRequest($entity, $isAjax = false){
        $app = App::i();
        
        $status = 200;
        try{
            $entity->save(true);
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
        
        if($app->request->isAjax() || $isAjax || $app->request->headers('MapasSDK-REQUEST')){
            $this->json($data, $status);
        }elseif(isset($this->getData['redirectTo'])){
            $app->redirect($this->getData['redirectTo'], $status);
        }else{
            $app->redirect($app->request()->getReferer(), $status);
        }
    }

    // ============= ACTIONS =============== //


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
     * Creates a new entity of the class with same name in the parent\Entities namespace
     *
     * This action requires authentication and outputs the json with the new entity or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId');
     * </code>
     */
    function POST_index(){
        $this->requireAuthentication();

        $entity = $this->getRequestedEntity();
        
        foreach($this->data as $field=>$value){
            $entity->$field = $value;
        }
        
        if($errors = $entity->validationErrors){
            $this->errorJson($errors);
        }else{
            $this->_finishRequest($entity);
        }
    }

    /**
     * Render the create form..
     *
     * This method requires authentication and renders the template 'create' of this controller
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId', 'create');
     * </code>
     *
     */
    function GET_create(){
        $this->requireAuthentication();

        $entity = $this->getRequestedEntity();
        
        $class = $this->entityClassName;

        $entity->status = $class::STATUS_DRAFT;

        $this->render('create', ['entity' => $entity]);
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
            $this->render('single', ['entity' => $entity]);
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

        if($entity->usesNested()){

            $child_entity_request = $app->repo('RequestChildEntity')->findOneBy(['originType' => $entity->getClassName(), 'originId' => $entity->id]);

            $this->render('edit', ['entity' => $entity, 'child_entity_request' => $child_entity_request]);

        }else{
            $this->render('edit', ['entity' => $entity]);
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
    function PUT_single(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        //Atribui a propriedade editada
        foreach($this->postData as $field=>$value){
            $entity->$field = $value;
        }

        if($errors = $entity->validationErrors){
            $this->errorJson($errors);
        }else{
            $this->_finishRequest($entity);
        }
    }


    function PATCH_single(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        //Atribui a propriedade editada
        foreach($this->postData as $field=>$value){
            $entity->$field = $value;
        }

        if($_errors = $entity->validationErrors){
            $errors = [];
            foreach($this->postData as $field=>$value){
                if(key_exists($field, $_errors)){
                    $errors[$field] = $_errors[$field];
                }
            }

            if($errors){
                $this->errorJson($errors, 400);
            }
        }

        $this->_finishRequest($entity);
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
//        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $single_url = $entity->singleUrl;

        $entity->delete(true);

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $redirect_url = $app->request()->getReferer();
            if($redirect_url === $single_url)
                $redirect_url = $app->createUrl ('panel');
            
            $app->applyHookBoundTo($this, "DELETE({$this->id}):beforeRedirect", [$entity, &$redirect_url]);

            $app->redirect($redirect_url);
        }
    }

    /*
     * Prints a json with the properties metadata of the entiry related to this controller.
     *
     * @see \MapasCulturais\Entity::getPropertiesMetadata()
     */
    public function GET_propertiesMetadata(){
        $class = $this->entityClassName;
        echo json_encode($class::getPropertiesMetadata());
    }
}
