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
    public function POST_index($data = null) {
        $app = App::i();

        $app->hook('entity(seal).insert:before', function() use($app) {
            $this->owner = $app->user->profile;
        });
        parent::POST_index($data);
    }

    public function GET_sealRelation(){
    	$app = App::i();
    	$id = $this->data['id'];
    	$relation = $app->repo('SealRelation')->find($id);
        $expirationDate = $this->VerifySealValidity($relation);
        
    	$this->render('sealrelation', ['relation'=>$relation, 'expirationDate'=>$expirationDate]);
    }

    public function GET_printSealRelation(){
        $app = App::i();

    	$id = $this->data['id'];
    	$rel = $app->repo('SealRelation')->find($id);
        
        if(!$rel){
            $app->pass();
        }

        $rel->checkPermission('print');
        
    	$this->render('printsealrelation', ['relation' => $rel]);

    }

    /**
     * Verifica a validade do selo a ser exibido
     * 
     * @param [entity] $relation - entity com a relacao doador/receptor do selo
     * @return Array or Boolean
     */
    private function VerifySealValidity($relation){
        if($relation->seal->validPeriod > 0){
            $today = new \DateTime();
            $expirationDate = date_add($relation->seal->createTimestamp, date_interval_create_from_date_string($relation->seal->validPeriod . " months"));
            $expirated = $expirationDate < $today;
            $date = array('expirated'=>$expirated, 'date'=>$expirationDate);

            return $date;
        }        

        return false;
    }
}
