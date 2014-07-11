<?php
use MapasCulturais\App;
$app = MapasCulturais\App::i();

return array(
    'issue 212' => function() use ($app){
        $app->hook('entity(<<event|space|project|agent>>).remove:before', function() use($app){
            $app->log->info(" >> REMOVING $this->className ($this->id) - $this->name" );
            
            if($this->className === 'MapasCulturais\Entities\Event'){
                foreach($this->occurrences as $occ)
                    $app->log->info("   >> >>  Space name: {$occ->space->name}" );
            }
        });
        
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

                $app->log->info('Movendo os eventos do espaço ' . $space->name . ' para o espaço ' . $destino->name . ' STATUS: ' . $destino->status);

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
        
        foreach($agente_virada_cultural_lixo->projects as $project){
            $project->owner = $secretaria_oficial;
            $project->save();
        }
        
        $app->em->refresh($agente_virada_cultural_lixo);
        
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
        }

        $app->em->flush();
       
        $app->em->refresh($secretaria_lixo);
        $secretaria_lixo->destroy();

        return true;
    },

    'issue 224' => function() use ($app) {
        $app->log->info(' ');
        $app->log->info('ATUALIZANDO NO DB OS PREÇOS DAS OCORRÊNCIAS (issue #224 - https://github.com/hacklabr/mapasculturais/issues/224');
        $app->log->info(' ');
        $events = $app->repo('Event')->findAll();
        $evtCount = 0;
        $occCount = 0;
        foreach($events as $event){
            $evtCount++;
            foreach($event->occurrences as $occ){
                $occCount++;
                $rule = (array) $occ->rule;
                if(!empty($rule['price'])){
                    $app->log->info(str_pad($occCount,4).' EVT '.str_pad($event->id,5).' OCRR. '.str_pad($occ->id,5).' JA POSSUÍA $rule->price      "'.$occ->rule->price.'"');
                }else{
                    $rule['price'] = $event->preco ? $event->preco : '';
                    $occ->rule = $rule;
                    $app->log->info(str_pad($occCount,4).'EVT '.str_pad($event->id,5).' OCRR. '.str_pad($occ->id,5).' ALTERADO   $rule->price PARA "'.$occ->rule->price.'"');
                    $occ->save();
                }
            }
        }
        $app->log->info(' ');
        $app->log->info(str_pad($occCount,4).' occurrence->rule atualizadas em '.str_pad($evtCount,4).' eventos (issue #224 - https://github.com/hacklabr/mapasculturais/issues/224');
        $app->log->info(' ');
        $app->log->info('Removendo Event Metadata "preco" ');

        $eventPrecos = $app->repo('EventMeta')->findBy(array('key'=>'preco'));
        $evtMetaCount = 0;
        foreach($eventPrecos as $preco){
            $evtMetaCount++;
            $preco->delete();
        }
        $app->log->info(str_pad($evtMetaCount,4).' remoções.');
        $app->log->info(' ');
        $app->log->info('OPERAÇÃO FINALIZADA');
        $app->em->flush();

        return true;
    }

);