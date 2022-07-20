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
        Traits\ControllerChangeOwner,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI;

    /**
     * @api {GET} /api/seal/describe Recuperar descrição da entidade Selo
     * @apiUse APIdescribe
     * @apiGroup SEAL
     * @apiName GETdescribe
     */

    /**
     * @api {POST} /seal/index Criar selo.
     * @apiUse APICreate
     * @apiGroup SEAL
     * @apiName POSTseal
     */

    /**
     * @api {POST} /seal/index Criar selo.
     * @apiUse APICreate
     * @apiGroup SEAL
     * @apiName POSTseal
     */

     /**
     * @api {PATCH} /seal/single/:id Atualizar parcialmente um selo.
     * @apiUse APIPatch
     * @apiGroup SEAL
     * @apiName PATCHseal
     */

    /**
     * @api {PUT} /seal/single/:id Atualizar selo.
     * @apiUse APIPut
     * @apiGroup SEAL
     * @apiName PUTseal
     */

     /**
     * @api {PUT|PATCH} /seal/single/:id Deletar selo.
     * @apiUse APIDelete
     * @apiGroup SEAL
     * @apiName DELETEseal
     */

     /**
     * @api {all} /api/seal/getTypes Retornar tipos
     * @apiUse getTypes
     * @apiGroup SEAL
     * @apiName getTypes
     * @apiSuccessExample {json} Success-Response:
     * [{
     *   "id": 0,
     *   "name": "Infinita"
     * }, {
     *   "id": 1,
     *   "name": "Dias"
     * }, {
     *   "id": 2,
     *   "name": "Semanas"
     * }]
     *
     */

    /**
     * @api {all} /api/seal/getTypeGroups Retornar grupos
     * @apiUse getTypeGroups
     * @apiGroup SEAL
     * @apiName getTypeGroups
     */


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

    public function GET_sealRelation()
    {
        $app = App::i();
        $id = $this->data['id'];
        $relation = $app->repo('SealRelation')->find($id);
        if (!$relation) {
            $app->pass();
        }
        $mensagemPrintSealRelation = $relation->getCertificateText(true);
        $this->render('sealrelation', [
            'relation' => $relation,
            'printSeal' => $mensagemPrintSealRelation,
            'seal' => $relation->seal]);
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

}
