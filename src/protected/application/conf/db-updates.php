<?php
use MapasCulturais\App;
$app = MapasCulturais\App::i();

return array(
    'issue 212' => function() use ($app){
        // destino => array de espacos que vao ser removidos
        $remover_espacos = [471, 491, 328, 516, 526];
        
        $espacos = [
            '331' => [330],
            '175' => [518],
            '153' => [346],
            '349' => [350],
            '193' => [355],
            '169' => [357, 356, 487],
            '195' => [123, 533],
            '197' => [358],
            '527' => [361],
            '28'  => [365],
            '377' => [524],
            '183' => [400],
            '530' => [422],
            '524' => [377],
            '485' => [381],
            '488' => [402],
            '390' => [340, 417, 418],
            '426' => [427],
            '474' => [479],
            '429' => [333],
            '430' => [538],
            '437' => [436],
            '409' => [432],
            '452' => [410],
            '453' => [411],
            '532' => [531, 442],
            '219' => [459],
            '207' => [463]
        ];
        $repo = $app->repo('Space');
        
        foreach($espacos as $destino_id => $origens){
            $destino = $repo->find($destino_id);
            
            foreach($origens as $space_id){
                $remover_espacos[] = $space_id;
                
                $space = $repo->find($space_id);
                
                $occurrences = $app->repo('EventOccurrence')->findBy(['space' => $space]);
                
                $app->log->info('Movendo os eventos do espaço ' . $space->name . ' para o espaço ' . $destino->name);
                
                foreach($occurrences as $oc){
                    $oc->space = $destino;
                    $oc->save();
                }
            }
        }
        
        $app->em->flush();
        
        $secretaria_oficial = $app->repo('Agent')->find(425);
        $secretaria_lixo = $app->repo('Agent')->find(433);
        
        $agente_virada_cultural_lixo = $app->repo('Agent')->find(428);
        $agente_virada_cultural_lixo->destroy();
        
        foreach($secretaria_lixo->spaces as $entity){
            $app->log->info('Movendo o espaço ' . $entity->name . ' para a secretaria oficial');
            
            $entity->owner = $secretaria_oficial;
            $entity->save();
        }
        
        foreach($secretaria_lixo->events as $entity){
            $app->log->info('Movendo o evento ' . $entity->name . ' para a secretaria oficial');
            
            $entity->owner = $secretaria_oficial;
            $entity->save();
        }
        
        foreach($secretaria_lixo->projects as $entity){
            $app->log->info('Movendo o projeto ' . $entity->name . ' para a secretaria oficial');
            
            $entity->owner = $secretaria_oficial;
            $entity->save();
        }
        
        $app->em->flush();
        
        
        foreach($remover_espacos as $space_id){
            $space = $repo->find($space_id);
            
            $app->log->info('Removendo o espaço ' . $space->name);
                
            $space->destroy();
        };
        
        $app->em->flush();
        
        
        $secretaria_lixo->destroy();
    },
);