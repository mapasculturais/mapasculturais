<?php
namespace MapasCulturais;

$app = App::i();
return array(
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
        
    }
);