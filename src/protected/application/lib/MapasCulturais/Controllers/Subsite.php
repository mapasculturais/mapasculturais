<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * Subsite Controller
 *
 * By default this controller is registered with the id 'subsite'.
 *
 *  @property-read \MapasCulturais\Entities\Subsite $requestedEntity The Requested Entity
 *
 */
class Subsite extends EntityController {
    use
    	Traits\ControllerUploads,
    	Traits\ControllerTypes,
      Traits\ControllerVerifiable,
      Traits\ControllerSoftDelete,
      Traits\ControllerDraft,
      Traits\ControllerArchive,
      Traits\ControllerAPI;

      /**
       * Creates a new Subsite
       *
       * This action requires authentication and outputs the json with the new event or with an array of errors.
       *
       * <code>
       * // creates the url to this action
       * $url = $app->createUrl('subsite');
       * </code>
       */
      function POST_index(){
        $app = App::i();

        $app->hook('entity(subsite).insert:before', function() use($app){
          $this->owner = $app->user->profile;
        });

        parent::POST_index();
      }
}
