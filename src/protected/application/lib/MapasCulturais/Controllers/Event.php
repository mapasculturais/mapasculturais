<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Event Controller
 *
 * By default this controller is registered with the id 'event'.
 *
 */
class Event extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSealRelation,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerOpportunities;


    /**
     * @api {all} /api/agent/findOne Buscar Evento
     * @apiUse APIfindOne
     * @apiGroup EVENT
     * @apiName apiFindOne
     *
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost:8090/api/event/findOne?@select=id,name,subsite.name&user=EQ\(8006\)
     */


    /**
     * @api {GET} /api/event/describe Recuperar descrição da entidade Evento
     * @apiUse APIdescribe
     * @apiGroup EVENT
     * @apiName GETdescribe
     */


    /**
     * @api {POST} /event/index Criar Evento.
     * @apiUse APICreate
     * @apiGroup EVENT
     * @apiName POSTevent
     */

     /**
     * @api {PATCH} /event/single/:id Atualizar parcialmente um evento.
     * @apiUse APIPatch
     * @apiGroup EVENT
     * @apiName PATCHevent
     */

    /**
     * @api {PUT} /event/single/:id Atualizar evento.
     * @apiUse APIPut
     * @apiGroup EVENT
     * @apiName PUTevent
     */

     /**
     * @api {DELETE} /event/single/:id Deletar evento.
     * @apiUse APIDelete
     * @apiGroup EVENT
     * @apiName DELETEevent
     */

    /**
     * @api {all} /api/event/getTypes Retornar tipos
     * @apiUse getTypes
     * @apiGroup EVENT
     * @apiName getTypes
     * @apiSuccessExample {json} Success-Response:
     * [{
     *   "id": 1,
     *   "name": "Padrão"
     *  }]
     *
     */

    /**
     * @api {all} /api/event/getTypeGroups Retornar grupos
     * @apiUse getTypeGroups
     * @apiGroup EVENT
     * @apiName getTypeGroups
     */

    /**
     * Creates a new Event
     *
     * This action requires authentication and outputs the json with the new event or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('agent');
     * </code>
     */
    public function POST_index($data = null) {
        $app = App::i();

        $app->hook('entity(event).insert:before', function() use($app){
            $this->owner = $app->user->profile;
        });
        parent::POST_index($data);
    }

    /**
     * @api {all} /api/event/findOccurrences Pesquisa de ocorrências
     * @apiDescription Realizar a pesquisa por ocorrências de eventos em determinado período
     * @apiGroup EVENT
     * @apiName findOccurrences
     * @apiParam {date} [@from=HOJE] Data inicial do período
     * @apiParam {date} [@to=HOJE] Data final do período
     * @apiParam {String} [@limit] Limite para a quantidade de registro
     * @apiParam {String} [@offset] Offset para desolamento da quantidade de registros
     * @apiParam {String} [@page] Número da página a ser retornada
     * @apiParam {String} [space:atributo] Realizar a pesquisa das ocorrências de eventos por espaço.
     *
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/event/findOccurrences?@from=2016-05-01&@to=2016-05-31&space:id=EQ(8915)
     */
    function API_findOccurrences(){
        $result = $this->apiFindOccurrences($this->getData);

        // @TODO: set headers to
        $this->apiResponse($result);
    }

    function apiFindOccurrences($query_data){
        $app = App::i();
        $rsm = new ResultSetMapping();


        $rsm->addScalarResult('id', 'occurrence_id');
        $rsm->addScalarResult('event_id', 'event_id');
        $rsm->addScalarResult('space_id', 'space_id');
        $rsm->addScalarResult('starts_on', 'starts_on');
        $rsm->addScalarResult('starts_at', 'starts_at');
        $rsm->addScalarResult('ends_on', 'ends_on');
        $rsm->addScalarResult('ends_at', 'ends_at');
        $rsm->addScalarResult('rule', 'rule');


        // find occurrences

        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;
        
        $event_attendance_user = key_exists('@attendanceUser', $query_data) ? $query_data['@attendanceUser'] : $app->user->id;
        unset($query_data['@attendanceUser']);

        $subsite_sql = '';

        if($subsite = $app->getCurrentSubsite()){
            $space_ids = $app->repo('Space')->getCurrentSubsiteSpaceIds(true);

            $subsite_sql = "AND space_id IN ({$space_ids})";
        }

        $query = $app->em->createNativeQuery("
            SELECT id, event_id, space_id, starts_on, starts_at::TIME AS starts_at, ends_on, ends_at::TIME AS ends_at, rule
            FROM recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
            WHERE status > 0 $subsite_sql

            ORDER BY starts_on, starts_at", $rsm);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        if($app->config['app.useEventsCache']){
            $query->useResultCache(true, $app->config['app.eventsCache.lifetime']);
        }

        $_result = $query->getScalarResult();

        $space_query_data = [];
        $event_query_data = $query_data;

        // filter spaces


        foreach($this->getData as $key => $val){
            if(strtolower(substr($key, 0, 6)) === 'space:'){
                $space_query_data[substr($key, 6)] = $val;
                unset($event_query_data[$key]);
            }
        }

        unset(
                $space_query_data['@limit'],
                $space_query_data['@offset'],
                $space_query_data['@page']
        );

        $space_ids = [-1];

        foreach($_result as $occ){
            $space_ids[] = $occ['space_id'];
        }

        $space_ids = implode(',',$space_ids);
        if(isset($space_query_data['id'])){
            $space_query_id = $space_query_data['id'];
            $space_query_data['id'] = "AND({$space_query_id},IN({$space_ids}))";
        }else{
            $space_query_data['id'] = "IN({$space_ids})";
        }

        if(isset($space_query_data['@select'])){
            $props = explode(',', $space_query_data['@select']);
            if(array_search('id', $props) === false){
                $space_query_data['@select'] .= ',id';
            }
        }

        $space_controller = $app->controller('space');
        $spaces = $space_controller->apiQuery($space_query_data);

        $spaces_by_id = [];
        foreach($spaces as $space){
            $spaces_by_id[$space['id']] = $space;
        }

        // filter events

        unset(
                $event_query_data['@from'],
                $event_query_data['@to'],
                $event_query_data['@limit'],
                $event_query_data['@offset'],
                $event_query_data['@page']
        );

        $event_ids = [];

        foreach($_result as $occ){
            if(isset($spaces_by_id[$occ['space_id']])){
                $event_ids[] = $occ['event_id'];
            }
        }

        if($event_ids){

            $event_ids = implode(',',$event_ids);

            if(isset($event_query_data['id'])){
                $event_query_id = $event_query_data['id'];
                $event_query_data['id'] = "AND({$event_query_id},IN({$event_ids}))";
            }else{
                $event_query_data['id'] = "IN({$event_ids})";
            }
            if(isset($event_query_data['@select'])){
                $props = explode(',', $event_query_data['@select']);
                if(array_search('id', $props) === false){
                    $event_query_data['@select'] .= ',id';
                }
            }
            $event_controller = $app->controller('event');

            $events = $event_controller->apiQuery($event_query_data);

            $events_by_id = [];
            foreach($events as $event){
                $events_by_id[$event['id']] = $event;
            }

            $result = [];

            foreach($_result as $i => $occ){

                $space_id = $occ['space_id'];
                $event_id = $occ['event_id'];

                if(!isset($events_by_id[$event_id]))
                    continue;


                unset($occ['space_id']);
                
                if(isset($spaces_by_id[$space_id]) && isset($events_by_id[$event_id])){
                    unset($occ['event']);

                    $space = $spaces_by_id[$space_id];
                    $event = $events_by_id[$event_id];
                    $occ['rule'] = json_decode($occ['rule']);

                    $occ['space'] = $space;

                    $item = array_merge($event, $occ);

                    $result[] = $item;
                }
            }

            // pagination

            $offset = isset($this->getData['@offset']) ? $this->getData['@offset'] : null;
            $limit = isset($this->getData['@limit']) ? $this->getData['@limit'] : null;
            $page = isset($this->getData['@page']) ? $this->getData['@page'] : null;

            if($page && $limit){
                $offset = (($page - 1) * $limit);
                $result = array_slice($result, $offset, $limit);
            } else if($offset && $limit){
                $result = array_slice($result, $offset, $limit);
            } else if ($offset) {
                $result = array_slice($result, $offset);
            } else if ($limit) {
                $result = array_slice($result, 0, $limit);
            }
        } else {
            $result = [];
        }


        $reccurrence_strings = [];
        foreach($result as &$r){
            $r['attendance'] = null;
            $r['_reccurrence_string'] = "{$r['occurrence_id']}.{$r['starts_on']}.{$r['starts_at']}.{$r['ends_on']}.{$r['ends_at']}";
            $reccurrence_strings[$r['_reccurrence_string']] = &$r;
        }

        if($reccurrence_strings){
            if(!is_numeric($event_attendance_user)){
                $user = $app->repo('User')->getByProcurationToken($event_attendance_user);
                if($user){
                    $event_attendance_user = $user->id;
                } else {
                    $event_attendance_user = null;
                }
            }
            if($event_attendance_user){
                $_reccurrence_strings = implode(',', array_keys($reccurrence_strings));
    
                $attendances = $app->controller('eventAttendance')->apiQuery([
                    '@select' => 'id,type,reccurrenceString,user,eventOccurrence,event,space',
                    'reccurrenceString' => "IN($_reccurrence_strings)",
                    'user' => "EQ($event_attendance_user)"
                ]);

                foreach($attendances as $attendance){
                    $reccurrence_strings[$attendance['reccurrenceString']]['attendance'] = $attendance;
                }
            }
        }
        
        return $result;
    }

    function GET_create() {
        if(key_exists('projectId', $this->urlData) && is_numeric($this->urlData['projectId'])){
            $project = $this->repository->find($this->urlData['projectId']);
            if($project)
                App::i()->hook('entity(event).new', function() use ($project){
                    $this->project = $project;
                });
        }
        parent::GET_create();
    }

    /**
     * @api {all} /api/event/findBySpace Pesquisar eventos por espaço
     * @apiDescription Realizar a pesquisa de eventos por espaço
     * @apiGroup EVENT
     * @apiName findBySpace
     * @apiParam {String} spaceId ID do espaço.
     * @apiParam {date} [@from=HOJE] Data inicial do período
     * @apiParam {date} [@to=HOJE] Data final do período
     * @apiParam {--} [@count] Faz com que seja retornado a quantidade de registros
     *
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/event/findBySpace?@from=2016-05-01&@to=2016-05-31&spaceId=8915
     *   curl -i http://localhost/api/event/findBySpace?@from=2016-05-01&@to=2016-05-31&spaceId=8915&@count
     */
    function API_findBySpace(){
        if(!key_exists('spaceId', $this->data)){
            $this->errorJson('spaceId is required');
            return;
        }

        $query_data = $this->getData;

        $spaceId = $query_data['spaceId'];
        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;

        unset(
            $query_data['@from'],
            $query_data['@to'],
            $query_data['spaceId'],
            $query_data['_geoLocation']
        );

        $app = App::i();
        $space = $app->repo('Space')->find($spaceId);

        if(!$space){
            $this->errorJson('space not found');
            return;
        }

        $occurrences = [];
        $occurrences_readable = [];

        $events = $app->repo('Event')->findBySpace($space, $date_from, $date_to);

        $event_ids = array_map(function($e) {
            return $e->id;
        }, $events);

        $_occurrences = $app->repo('EventOccurrence')->findByEventsAndSpaces($events, [$space], $date_from, $date_to);

        foreach($events as $e){
            $occurrences_readable[$e->id] = [];

            if(!is_array($_occurrences)){
                continue;
            }

            $occurrences[$e->id] = array_filter($_occurrences, function($eo) use ($e){
                return $e->id == $eo->eventId;
            });

            $occurrences_readable[$e->id] = array_map(function($occ) use ($app) {
                if(!empty($occ->rule->description)) {
                    return $occ->rule->description;
                }else{
                    return $occ->startsOn->format('d \d\e') . ' ' . \MapasCulturais\i::__($occ->startsOn->format('F')) . ' às ' . $occ->startsAt->format('H:i');
                }
            }, $occurrences[$e->id]);

        }

        if($event_ids){
            $event_data = $query_data;
            $event_data['id'] = 'IN(' . implode(',', $event_ids) .')';

            $result = $this->apiQuery($event_data);

            if(is_array($result)){
                foreach($result as $k => $e){
                    //@TODO: verify if occurrences and readable occurrences were selected in query data
                    $result[$k]['occurrences'] = key_exists($e['id'], $occurrences) ? $occurrences[$e['id']] : [];
                    $result[$k]['readableOccurrences'] = key_exists($e['id'], $occurrences_readable) ? $occurrences_readable[$e['id']] : [];
                }
            }

            $this->apiResponse($result);
        }else{
            $this->apiResponse(key_exists('@count', $query_data) ? 0 : []);
        }
    }

    function apiQueryByLocation($query_data){
        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;
        $spaces     = key_exists('space',   $query_data) ? $query_data['space'] : null;

        unset(
            $query_data['@from'],
            $query_data['@to'],
            $query_data['space']

        );

        $space_data = [];

        if($spaces){
            $space_data['id'] = $spaces;
        }

        foreach($query_data as $key => $val){
            if(substr($key, 0, 6) === 'space:'){
                $space_data[substr($key, 6)] = $val;
                unset($query_data[$key]);
            }
        }

        if(key_exists('_geoLocation', $query_data) || $space_data){
            $space_controller = App::i()->controller('space');

            $space_data['@select'] = 'id';

            if(key_exists('_geoLocation', $query_data)){
                $space_data['_geoLocation'] = $this->data['_geoLocation'];
            }

            $space_ids = array_map(
                function($e){
                    return $e['id'];

                },
                $space_controller->apiQuery($space_data)
            );
            $events = $this->repository->findBySpace($space_ids, $date_from, $date_to);

            unset($query_data['_geoLocation']);
        }else{
            $events = $this->repository->findByDateInterval($date_from, $date_to);
        }

        $event_ids = array_map(function($e) {
            return $e->id;
        }, $events);

        $result_occurrences = [];

        foreach($events as $evt){
            $e = [];

            $e['spaces'] = [];
            $e['occurrences'] = [];
            $e['occurrencesReadable'] = [];

            $result_occurrences[$evt->id] = $e;
        }

        if($event_ids){

            $query_data['id'] = 'IN(' . implode(',', $event_ids) .')';
            // @TODO: verificar se o @select tem o id
            $result = $this->apiQuery($query_data);
            if(is_array($result)){
                foreach($result as $k => $r){
                    if(isset($result_occurrences[$r['id']])){
                        $result[$k] = array_merge($result_occurrences[$r['id']], $r);
                    }
                }
            }
        }else{
            $result = key_exists('@count', $query_data) ? 0 : [];
        }

        return $result;
    }

    /**
     * @api {all} /api/event/findByLocation Pesquisar por localização
     * @apiDescription Realizar a pesquisa de eventos por localização
     * @apiGroup EVENT
     * @apiName findByLocation
     * @apiParam {String} [space] ID do espaço a ser utilizado como filtro na busca.
     * @apiParam {String} [space:atributo] Realizar a pesquisa das ocorrências de eventos por espaço.
     * @apiParam {date} [@from=HOJE] Data inicial do período
     * @apiParam {date} [@to=HOJE] Data final do período
     * @apiParam {--} _geoLocation Argumento com geolocalização
     * @apiParam {--} [@count] Faz com que seja retornado a quantidade de registros
     *
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/event/findBySpace?@from=2016-05-01&@to=2016-05-31&spaceId=8915
     *   curl -i http://localhost/api/event/findBySpace?@from=2016-05-01&@to=2016-05-31&spaceId=8915&@count
     */
    function API_findByLocation(){

        $this->apiResponse($this->apiQueryByLocation($this->getData));

    }
}
