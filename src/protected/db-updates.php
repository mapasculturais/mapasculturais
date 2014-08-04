<?php
namespace MapasCulturais;

$app = App::i();
return array(
    'teste de execução pelo deploy' => function() use($app){
        $agents = $app->repo('Agent')->findAll();

        foreach($agents as $a){
            echo "AGENTE: $a->name\n";
        }
    },

    'Virada 2014 - set events is verified' => function() use($app){
        $virada = $app->repo('Project')->find(4);

        $events = $app->repo('Event')->findBy(array('project' => $virada));

        $i = 0;
        foreach($events as $event){
            $i++;
            echo "$i - Definindo o evento \"{$event->name}\" como oficial.\n";
            $event->isVerified = true;
            $event->save();
        }

        $app->em->flush();
    },

    'Transform rule.duration to minutes and save the endsAt data' => function() use($app){
        $occurrences = $app->repo('EventOccurrence')->findAll();

        foreach($occurrences as $o){
            $value = (array) $o->rule;
            if(!empty($value['duration'])){
                @list($hours, $minutes) = explode('h', $value['duration']);
                $value['duration'] = intval($minutes) + intval($hours) * 60;
            }
            $value['endsAt'] = $o->endsAt->format('H:i');
            $o->rule = $value;
            echo "ev occurence start: {$value['startsAt']} for: {$value['duration']} minutes ends: {$value['endsAt']}\n";
            $o->save();
        }
    },

);
