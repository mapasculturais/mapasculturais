<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * SaaS Controller
 *
 * By default this controller is registered with the id 'saas'.
 *
 *  @property-read \MapasCulturais\Entities\SaaS $requestedEntity The Requested Entity
 *
 */
class SaaS extends EntityController {
    use
    	Traits\ControllerUploads,
    	Traits\ControllerTypes,
      Traits\ControllerMetaLists,
      Traits\ControllerVerifiable,
      Traits\ControllerSoftDelete,
      Traits\ControllerDraft,
      Traits\ControllerAPI;

      /**
       * Creates a new SaaS
       *
       * This action requires authentication and outputs the json with the new event or with an array of errors.
       *
       * <code>
       * // creates the url to this action
       * $url = $app->createUrl('saas');
       * </code>
       */
      function POST_index(){
        $app = App::i();

        $app->hook('entity(saas).insert:before', function() use($app){
          $this->owner = $app->user->profile;
        });

        parent::POST_index();
      }
}
