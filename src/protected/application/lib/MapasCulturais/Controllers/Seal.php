<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * Seal Controller
 *
 * By default this controller is registered with the id 'seal'.
 *
 *  @property-read \MapasCulturais\Entities\Project $requestedEntity The Requested Entity
 *
 */
class Seal extends EntityController {
    use	 
    	Traits\ControllerUploads,
    	Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerVerifiable,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft,
        Traits\ControllerAPI;
 
	/**
	 * Creates a new Seal
	 *
	 * This action requires authentication and outputs the json with the new event or with an array of errors.
	 *
	 * <code>
	 * // creates the url to this action
	 * $url = $app->createUrl(seal');
	 * </code>
	 */
	function POST_index(){
		$app = App::i();
	
		$app->hook('entity(seal).insert:before', function() use($app){
			$this->owner = $app->user->profile;
		});
			parent::POST_index();
	}
        
    function ALL_setAsUserProfile(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal)
            $app->pass();

        $success = $seal->setAsUserProfile();

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_addRole(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal || !$this->data['role'])
            $app->pass();

        $success = $seal->user->addRole($this->data['role']);

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_removeRole(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal || !$this->data['role'])
            $app->pass();

        $success = $seal->user->removeRole($this->data['role']);

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }
    
    function GET_sealRelation(){
    	$app = App::i();
    	
    	$id = $this->data['id'];
    	
    	$rel = $app->repo('SealRelation')->find($id);
    	
    	die($rel->dump());
    }
}
