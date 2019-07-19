<?php

use MapasCulturais\Entities;

class MapasCulturais_TestFactory{
    protected $entitiesCount = [
        'Agent' => 0,
        'Space' => 0,
        'Project' => 0,
        'Event' => 0,
        'Opportunity' => 0,
        'Seal' => 0
    ];

    /**
     * Instance of App
     *
     * @var \MapasCulturais\App
     */
    public $app;

    public $autoPersist = false;

    function __construct(MapasCulturais\App $app) {
        $this->app = $app;
    }
    
    function getEntityProperties($entity, $props = []){
        $result = [];
        $entity_num = $this->entitiesCount[$entity];
        foreach($props as $prop){
            if($prop == 'owner') {
                if($this->app->auth->isUserAuthenticated()){
                    $result[$prop] = $this->app->user->profile;
                }
            } elseif($prop == 'type') {
                $_types = $this->app->getRegisteredEntityTypes("MapasCulturais\\Entities\\{$entity}");
                $result[$prop] = array_shift($_types);
            } else {
                $result[$prop] = "{$entity} {$prop} {$entity_num}";
            }
        }

        return $result;
    }

    function createEntity($class, $properties = []){
        if(!class_exists($class) && class_exists('MapasCulturais\\Entities\\' . $class)){
            $class = 'MapasCulturais\\Entities\\' . $class;
        }

        $entity = new $class;

        foreach($properties as $prop => $val){
            $entity->$prop = $val;
        }

        $count_key = str_replace('MapasCulturais\\Entities\\', '', $class);

        if(!isset($this->entitiesCount[$count_key])) $this->entitiesCount[$count_key] = 0;
        
        $this->entitiesCount[$count_key]++;

        if($this->autoPersist){
            $entity->save(true);
        }

        return $entity;
    }

    function createAgent($properties = []){
        $class = 'Agent';
        $properties = $properties + $this->getEntityProperties($class, ['name', 'type', 'shortDescription']);

        $entity = $this->createEntity($class, $properties);

        return $entity;
    }

    function createSpace($properties = []){
        $class = 'Space';
        $properties = $properties + $this->getEntityProperties($class, ['name', 'type', 'shortDescription', 'owner']);

        $entity = $this->createEntity($class, $properties);

        return $entity;
    }

    function createProject($properties = []){
        $class = 'Project';
        $properties = $properties + $this->getEntityProperties($class, ['name', 'type', 'shortDescription', 'owner']);

        $entity = $this->createEntity($class, $properties);

        return $entity;
    }

    function createEvent($properties = []){
        $class = 'Event';
        $properties = $properties + $this->getEntityProperties($class, ['name', 'type', 'shortDescription', 'owner']);

        $entity = $this->createEntity($class, $properties);

        return $entity;
    }

    function parseEvent($event = null){
        if( is_null($event) ){
            $event = $this->createEvent();
        } else if( is_array($event) ){
            $event = $this->createEvent($event);
        } else if(!$event instanceof Entities\Event ){
            throw new \Exception('Invalid argument event');
        }

        return $event;
    }

    function parseDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            return $d;
        } else {
            return null;
        }
    }

    /**
     * cria uma ocorrência
     *
     * @param Entities\Event $event 
     * @param Entities\Space $space
     * @param string $starts
     * @param integer $duration
     * @param string $frequency
     * @return void
     */
    function createEventOccurrence($event, Entities\Space $space, string $starts, int $duration, string $frequency, $until = null, $weekdays = null){
        $starts = $this->parseDate($starts, 'Y-m-d H:i');
        if(!$starts){
            throw new \Exception('Invalid date format for argument $starts');
        }
        
        if($duration < 0){
            throw new \Exception('Duration must be a positive integer');
        }
        

        $endsAt = clone $starts;

        if($duration){
            $endsAt->add(new \DateInterval("PT{$duration}M"));
        }

        $rule = [
            "spaceId"   => $space->id,
            "startsOn"  => $starts->format('Y-m-d'),
            "startsAt"  => $starts->format('H:i'),
            "duration"  => $duration,
            "frequency" => $frequency,
            "description" => "description of event occurrence"
        ];

        if($until) {
            $rule['until'] = $until;
        }

        if($frequency == 'weekly'){
            if($weekdays){
                $object = [];
                foreach($weekdays as $day){
                    $object["$day"] = 'on';
                }
               $rule["day"] = (object) $object;
            }
        }

        $event_occurrence = $this->createEntity('EventOccurrence', [
            'space' => $space,
            'event' => $event,
            'rule' => $rule
        ]);

        return $event_occurrence;
    }

     /**
     * Cria um evento ou occorrência única para o evento informado.
     *
     * @param string $starts ex: 2019-04-23 22:00
     * @param int $duration in minutes
     * @param Entities\Space $space
     * @param array|Entities\Event $event 
     * @return MapasCulturais\Entities\Event
     */
    function createSingleEventOccurrence(string $starts, int $duration, Entities\Space $space, $event = null){
        $event_occurrence = $this->createEventOccurrence($event, $space, $starts, $duration, 'once');

        return $event_occurrence;
    }

    /**
     * Cria um evento diário ou occorrência diária para o evento informado.
     *
     * @param string $starts ex: 2019-04-23 22:00
     * @param int $duration in minutes
     * @param string $until ex: 2019-05-10
     * @param Entities\Space $space
     * @param array|Entities\Event $event 
     * @return MapasCulturais\Entities\Event
     */
    function createDailyEventOccurrence(string $starts, int $duration, string $until, Entities\Space $space, $event = null){
        
        $until = $this->parseDate($until, 'Y-m-d');
        
        if(!$until){
            throw new \Exception('Invalid date format for argument $until');
        }
        
        $event_occurrence = $this->createEventOccurrence($event, $space, $starts, $duration, 'daily', $until);

        $event_occurrence->setUntil($until);

        return $event_occurrence;
    }


    /**
     * Cria um evento semanal ou occorrência semanal para o evento informado.
     *
     * @param string $starts ex: 2019-04-23 22:00
     * @param int $duration in minutes
     * @param string $until ex: 2019-05-10
     * @param string $weekdays ex: [0,2,4] para domingo, terça e quinta
     * @param Entities\Space $space
     * @param array|Entities\Event $event 
     * @return MapasCulturais\Entities\Event
     */
    function createWeeklyEventOccurrence(string $starts, int $duration, string $until, array $weekdays, Entities\Space $space, $event = null){
        
        $until = $this->parseDate($until, 'Y-m-d');
        
        if(!$until){
            throw new \Exception('Invalid date format for argument $until');
        }
        
        $event_occurrence = $this->createEventOccurrence($event, $space, $starts, $duration, 'weekly', $until, $weekdays);

        $event_occurrence->setUntil($until);

        return $event_occurrence;
    }
}