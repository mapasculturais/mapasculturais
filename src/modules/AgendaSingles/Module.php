<?php

namespace AgendaSingles;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Module extends \MapasCulturais\Module{

    public function _init() {

        $app = App::i();

        $app->hook('GET(<<agent|space|project|registration>>.agendaSingle)', function() use ($app) {
            $entity = $this->requestedEntity;

            if (!$entity) {
                $app->pass();
            } elseif (!isset($this->getData['from']) || !isset($this->getData['to'])) {
                $app->stop();
            }
            
            $date_from = \DateTime::createFromFormat('Y-m-d', $this->getData['from']);
            $date_to = \DateTime::createFromFormat('Y-m-d', $this->getData['to']);

            if (!$date_from || !$date_to) {
                $app->stop();
            }

            if ($entity->className === 'MapasCulturais\Entities\Space') {
                $events = !$entity->id ? array() : $app->repo('Event')->findBySpace($entity, $date_from, $date_to);
            } elseif ($entity->className === 'MapasCulturais\Entities\Agent' || $entity->className === 'MapasCulturais\Entities\Registration' ) {
                if($entity->className === 'MapasCulturais\Entities\Registration') {
                    $events = !$entity->owner->id ? array() : $app->repo('Event')->findByAgent($entity->owner, $date_from, $date_to);
                } else {
                    $events = !$entity->id ? array() : $app->repo('Event')->findByAgent($entity, $date_from, $date_to);
                }
            } elseif ($entity->className === 'MapasCulturais\Entities\Project') {
                $events = !$entity->id ? array() : $app->repo('Event')->findByProject($entity, $date_from, $date_to);
            } else {
                $events = array();
            }

            if (empty($events)) {
                $app->stop();
            }
            $app->view->part('agenda-singles--content', array('events' => $events, 'entity' => $entity));
        });

        $app->hook('view.render(<<agent|space|project|registration>>/single):before', function() use ($app) {
            $app->view->enqueueScript('app', 'agenda-single', 'js/agenda-single.js', array('mapasculturais'));
            $app->view->localizeScript('agendaSingles', [
                'none' => \MapasCulturais\i::__('Nenhum')
            ]);
        });
        
        $app->hook('template(<<agent|space|project>>.single.tabs):end', function() use($app){
            $this->part('agenda-singles--tab');
        });
        
        $app->hook('template(<<agent|space|project|registration>>.<<single|view>>.tabs-content):end', function() use($app){
            $entity = $this->controller->requestedEntity;

            if ($entity->registrationFrom && $entity->registrationTo) {
                $date_from = $entity->registrationFrom;
                $date_to = $entity->registrationTo;
            } else {
                $date_create_timestamp = clone $entity->createTimestamp;
                $date_from = $entity->createTimestamp;
                $date_to = $date_create_timestamp->add(new \DateInterval('P90D'));
            }

            if($entity->className === 'MapasCulturais\Entities\Registration') {
                $entity = $this->controller->requestedEntity->owner;    
            }
            
            $this->part('agenda-singles', [
                'entity' => $entity,
                'date_from' => $date_from,
                'date_to' => $date_to
            ]);
        });
    }

    public function register() { }

}
