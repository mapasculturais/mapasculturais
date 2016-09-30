<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Seal;

/**
 * User Panel Controller
 *
 * By default this controller is registered with the id 'panel'.
 *
 */
class Panel extends \MapasCulturais\Controller {

    /**
     * Render the user panel.
     *
     * This method requires authentication and renders the template 'panel/index'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel');
     * </code>
     *
     */
    function GET_index(){
        $this->requireAuthentication();

        $app = App::i();

        $count = new \stdClass();

        $count->spaces		= $app->controller('space')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->agents		= $app->controller('agent')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->events		= $app->controller('event')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->projects	= $app->controller('project')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->seals		= $app->controller('seal')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);

        $this->render('index', ['count'=>$count]);
    }

    function GET_listUsers(){
        $this->requireAuthentication();

        $app = App::i();

        $roles = $app->getRoles();

        if (!$app->user->is('admin')) $app->user->checkPermission('addRole'); // dispara exceção se não for admin ou sueradmin

        $Repo = $app->repo('User');

        $vars = array();

        foreach ($roles as $roleSlug => $roleInfo) {
            $vars['list_' . $roleSlug] = $Repo->getByRole($roleSlug);

            if ($roleSlug == 'superAdmin') {
                $roles[$roleSlug]['permissionSuffix'] = 'SuperAdmin';
            } elseif ($roleSlug == 'admin') {
                $roles[$roleSlug]['permissionSuffix'] = 'Admin';
            } else {
                $roles[$roleSlug]['permissionSuffix'] = '';
            }

        }

        $vars['roles'] = $roles;
        $this->render('list-users', $vars);
    }

    protected function countEntity($entityName){
        $app = App::i();
        $entityClass = '\\MapasCulturais\\Entities\\' . $entityName;
        $dql = "SELECT COUNT(e.id) FROM $entityClass e JOIN e.owner o WHERE o.user = :user AND e.status >= 0";
        $query = $app->em->createQuery($dql);
        $query->setParameter('user', $app->user);
        $count = $query->getSingleScalarResult();
        $padded = str_pad($count, 2, '0', STR_PAD_LEFT);
        return $padded;
    }

    protected function _getUser(){
        $app = App::i();
        $user = null;
        if($app->user->is('admin') && key_exists('userId', $this->data)){
            $user = $app->repo('User')->find($this->data['userId']);


        }elseif($app->user->is('admin') && key_exists('agentId', $this->data)){
            $agent = $app->repo('Agent')->find($this->data['agentId']);
            $user = $agent->user;
        }
        if(!$user)
            $user = $app->user;

        return $user;
    }

    function GET_requireAuth(){
        $this->requireAuthentication();
        $this->render('require-authentication');
    }

    /**
     * Render the agent list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/agents'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'agents');
     * </code>
     *
     */
    function GET_agents(){
        $this->requireAuthentication();
        $user = $this->_getUser();

        $this->render('agents', ['user' => $user]);
    }

    protected function renderList($viewName, $entityName, $entityFields){
        $this->requireAuthentication();

        $user = $this->_getUser();

        $app = App::i();

        $user_filter = 'EQ(' . $user->id . ')';

        $query = [
            '@select' => $entityFields,
            '@files' => '(avatar.avatarSmall):url',
            'user' => $user_filter,
            'status' => 'EQ(' . Space::STATUS_ENABLED . ')',
            '@limit' => 50,
            '@order' => ''
        ];

        if(isset($this->data['keyword'])){
            $query['@keyword'] = $this->data['keyword'];
        }

        if(isset($this->data['order'])){
            $query['@order'] = $this->data['order'];
        } else {
            $query['@order'] = 'name ASC';
        }

        if(isset($this->data['page'])){
            $query['@page'] = intval($this->data['page']);
        } else{
            $query['@page'] = 1;
        }

        $controller = $app->controller($entityName);

        $enabled = $controller->apiQuery($query);
        $meta = $controller->lastQueryMetadata;
        $draft   = $controller->apiQuery(['@select' => $entityFields, '@files' => '(avatar.avatarSmall):url', 'user' => $user_filter, 'status' => 'EQ(' . Space::STATUS_DRAFT . ')', '@permissions' => 'view']);
        $trashed = $controller->apiQuery(['@select' => $entityFields, '@files' => '(avatar.avatarSmall):url', 'user' => $user_filter, 'status' => 'EQ(' . Space::STATUS_TRASH . ')', '@permissions' => 'view']);
        $archivedMethod = 'archived'.$entityName;
        $archived = $app->user->$archivedMethod;

        $enabled = json_decode(json_encode($enabled));
        $draft   = json_decode(json_encode($draft));
        $trashed = json_decode(json_encode($trashed));
        $archived= json_decode(json_encode($archived));

        $this->render($viewName, ['enabled' => $enabled, 'draft' => $draft, 'trashed' => $trashed, 'meta'=>$meta, 'archived' => $archived]);
    }

    /**
     * Render the space list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/spaces'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'spaces');
     * </code>
     *
     */
    function GET_spaces(){
        $fields = ['name', 'type', 'status', 'terms', 'endereco', 'singleUrl', 'editUrl',
                   'deleteUrl', 'publishUrl', 'unpublishUrl', 'acessibilidade', 'createTimestamp','archiveUrl','unarchiveUrl'];
        $app = App::i();
        $app->applyHook('controller(panel).extraFields(space)', [&$fields]);
        $this->renderList('spaces', 'space', implode(',', $fields));
    }

    /**
     * Render the event list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/events'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'events');
     * </code>
     *
     */
    function GET_events(){
        $fields = ['name', 'type', 'status', 'terms', 'classificacaoEtaria', 'singleUrl',
                   'editUrl', 'deleteUrl', 'publishUrl', 'unpublishUrl', 'createTimestamp','archiveUrl','unarchiveUrl'];
        $app = App::i();
        $app->applyHook('controller(panel).extraFields(event)', [&$fields]);
        $this->renderList('events', 'event', implode(',', $fields));
    }

    /**
     * Render the project list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/projects'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'projects');
     * </code>
     *
     */
    function GET_projects(){
        $this->requireAuthentication();
        $user = $this->_getUser();

        $this->render('projects', ['user' => $user]);
    }

    /**
     * Render the seal list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/seals'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'seals');
     * </code>
     *
     */
    function GET_seals(){
    	$this->requireAuthentication();
    	$user = $this->_getUser();

    	$this->render('seals', ['user' => $user]);
    }

    /**
     * Render the project list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/projects'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'registrations');
     * </code>
     *
     */
    function GET_registrations(){
        $this->requireAuthentication();
        $user = $this->_getUser();

        $this->render('registrations', ['user' => $user]);
    }

    /**
     * Render the project list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/projects'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'registrations');
     * </code>
     *
     */
    function GET_apps(){
        $this->requireAuthentication();
        $user = $this->_getUser();
        $enabledApps = App::i()->repo('UserApp')->findBy(['user' => $user, 'status' => \MapasCulturais\Entities\UserApp::STATUS_ENABLED]);
        $thrashedApps = App::i()->repo('UserApp')->findBy(['user' => $user, 'status' => \MapasCulturais\Entities\UserApp::STATUS_TRASH]);
        $this->render('apps', ['user' => $user, 'enabledApps' => $enabledApps, 'thrashedApps' => $thrashedApps]);
    }
}
