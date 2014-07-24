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
        
    }
);