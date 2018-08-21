<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Space Controller
 *
 * By default this controller is registered with the id 'space'.
 *
 */
class Space extends EntityController {
    use Traits\ControllerTypes,
        Traits\ControllerUploads,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSealRelation,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerAPINested,
        Traits\ControllerOpportunities;

    /**
     * @api {GET} /api/space/describe Recuperar descrição da entidade Espaço
     * @apiUse APIdescribe
     * @apiGroup SPACE
     * @apiName GETdescribe
     */

    /**
     * @api {POST} /api/space/index Criar espaço.
     * @apiUse APICreate
     * @apiGroup SPACE
     * @apiName POSTspace
     */

     /**

     * @api {PATCH} /api/space/single/:id Atualizar parcialmente um espaço.
     * @apiUse APIPatch
     * @apiGroup SPACE
     * @apiName PATCHspace
     */

    /**
     * @api {PUT} /api/space/single/:id Atualizar espaço.
     * @apiUse APIPut
     * @apiGroup SPACE
     * @apiName PUTspace
     */

     /**
     * @api {PUT|PATCH} /api/space/single/:id Deletar espaço.
     * @apiUse APIDelete
     * @apiGroup SPACE
     * @apiName DELETEspace
     */

    function GET_create() {
        if(key_exists('parentId', $this->urlData) && is_numeric($this->urlData['parentId'])){
            $parent = $this->repository->find($this->urlData['parentId']);
            if($parent)
                App::i()->hook('entity(space).new', function() use ($parent){
                    $this->parent = $parent;
                });
        }
        parent::GET_create();
    }

    /**
     * @api {all} /api/space/findByEvents Pesquisar espaços por evento
     * @apiDescription Realiza a pesquisa de espaços por evento
     * @apiGroup SPACE
     * @apiName findByEvents
     * @apiParam {date} [@from=HOJE] Data inicial do período
     * @apiParam {date} [@to=HOJE] Data final do período
     * @apiParam {String} [@select] usado para selecionar as propriedades da entidade que serão retornadas pela api.
     *                            Você pode retornar propriedades de entidades relacionadas. ex:( @select=id,name,
     *                            owner.name,owner.singleUrl)
     * @apiParam {--} _geoLocation Argumento com geolocalização
     * @apiParam {--} [@count] Faz com que seja retornado a quantidade de registros
     *
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/space/findByEvents?@from=2016-05-01&@to=2016-05-31&@select=*
     */
    function API_findByEvents(){
        $eventController = App::i()->controller('event');
        $query_data = $this->getData;

        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;

        unset(
            $query_data['@from'],
            $query_data['@to']
        );

        $event_data = ['@select' => 'id'] + $query_data;
        unset($event_data['@count']);
        $events_repo = App::i()->repo('Event');

        $_event_ids = $events_repo->findByDateInterval($date_from, $date_to, null, null, true);
        if (count($_event_ids) > 0) {
            $event_data['id'] = 'IN(' . implode(',', $_event_ids) . ')';

            $events = $eventController->apiQuery($event_data);
            $event_ids = array_map(function ($e){ return $e['id']; }, $events);

            $spaces = $this->repository->findByEventsAndDateInterval($event_ids, $date_from, $date_to);
            $space_ids = array_map(function($e){ return $e->id; }, $spaces);

            if($space_ids){
                $space_data = ['id' => 'IN(' . implode(',', $space_ids) .')'];
                foreach($query_data as $key => $val)
                    if($key[0] === '@' || $key == '_geoLocation')
                        $space_data[$key] = $val;

                unset($space_data['@keyword']);
                $response = $this->apiQuery($space_data);
            }else{
                $response = key_exists('@count', $query_data) ? 0 : [];
            }
        } else {
            $response = key_exists('@count', $query_data) ? 0 : [];
        }

        $this->apiResponse($response);
    }
}
