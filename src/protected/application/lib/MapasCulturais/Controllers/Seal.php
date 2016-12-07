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
 *  @property-read \MapasCulturais\Entities\Seal $requestedEntity The Requested Entity
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
        Traits\ControllerArchive,
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

    function GET_sealRelation(){
    	$app = App::i();

    	$id = $this->data['id'];

    	$rel = $app->repo('SealRelation')->find($id);

    	$this->render('sealrelation', ['relation' => $rel]);

    }

    function GET_printSealRelation(){
    	$app = App::i();

    	$id = $this->data['id'];
    	$rel = $app->repo('SealRelation')->find($id);

    	$this->requireAuthentication();
    	$user = $this->_getUser();

    	echo $this->render('printsealrelation', ['relation' => $rel]);

    }
}
