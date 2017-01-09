<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * EntityRevision Controller
 *
 * By default this controller is registered with the id 'entityrevision'.
 *
 */
class EntityRevision extends EntityController {

    function GET_history(){
    	$app = App::i();

    	$id = $this->data['id'];

    	$entityRevision = $app->repo('EntityRevision')->findCreateRevisionObject($id);

    	$this->render('history', ['entityRevision' => $entityRevision]);
    }
}
