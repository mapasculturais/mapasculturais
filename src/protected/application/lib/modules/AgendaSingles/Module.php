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
            $limit = (isset($this->getData['limit']))? $this->getData['limit'] : null;
            $offset = (isset($this->getData['offset']))? $this->getData['offset'] : null;
            $order = (isset($this->getData['orderDateDirection']))? $this->getData['orderDateDirection'] : 'ASC';

            if (!$date_from || !$date_to) {
                $app->stop();
            }

            $events = $app->repo('Event')->findByEntity($entity, $date_from, $date_to, $limit, $offset, $order);

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
            $date_from = new \DateTime();
            $date_to = new \DateTime('+30 days');
            
            $entity = $this->controller->requestedEntity;

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
