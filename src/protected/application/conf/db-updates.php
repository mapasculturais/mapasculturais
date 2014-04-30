<?php
use MapasCulturais\App;

return array(
    'importa programação virada cultural' => function(){
        $csv_to_array = function ($filename='', $delimiter=','){
            if(!file_exists($filename) || !is_readable($filename))
                return FALSE;

            $header = NULL;
            $data = array();
            if (($handle = fopen($filename, 'r')) !== FALSE)
            {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
                {
                    if(!$header)
                        $header = $row;
                    else
                        $data[] = array_combine($header, $row);
                }
                fclose($handle);
            }
            return $data;
        };

        $area_definition = $this->getRegisteredTaxonomy('MapasCulturais\Entities\Event', 'linguagem');


        $mapeamento_areas = array(
            'Exposições' => array('Artes Visuais'),
            'Cinema e Vídeo' => array('Audiovisual', 'Cinema')
        );

        $created_events = array();

        $agent_id = 428;
        $project_id = 4;

        $agent = App::i()->repo('Agent')->find($agent_id);
        $agent_errado = App::i()->repo('Agent')->find(427);

        $project = App::i()->repo('Project')->find($project_id);

        if(!$space_errado = $this->repo('Space')->findOneBy(array('name' => 'ESPAÇO DOS EVENTOS ERRADOS'))){
            $space_errado = new \MapasCulturais\Entities\Space;
            $space_errado->owner = $agent_errado;
            $space_errado->name = "ESPAÇO DOS EVENTOS ERRADOS";
            $space_errado->type = 501;
            $space_errado->terms['area'] = array('Outros');
            $space_errado->location = array(1,1);
            $space_errado->save();
        }

        $spaces = $csv_to_array(realpath(__DIR__ . '/../../../../tmp/virada-espacos.csv'));
        $events = $csv_to_array(realpath(__DIR__ . '/../../../../tmp/virada-eventos.csv'));


        $create_event = function($space, $event_data, $subtitle = '') use($mapeamento_areas, $created_events, $area_definition, $agent, $project){
            foreach($event_data as $k => $v)
                $event_data[$k] = trim($v);

            if(!$event_data['Atração'])
                return;

            $event = new \MapasCulturais\Entities\Event;
            $event->owner = $agent;
            $event->project = $project;
            $event->subTitle = $subtitle;

            $event->name = $event_data['Atração'];
            $event->shortDescription = array_key_exists('Sinopse (até 350 caracteres)', $event_data) ?
                    $event_data['Sinopse (até 350 caracteres)'] : '';

            if(array_key_exists('Preço ou ingresso', $event_data))
                $event->preco = $event_data['Preço ou ingresso'];

            $term = $event_data['Área de Atuação'];

            $event->terms['tag'] = array('Virada Cultural', $term);

            if(in_array($term, $area_definition->restrictedTerms) || in_array(strtolower($term), $area_definition->restrictedTerms)){
                $event->terms['area'] = array($term);
            }elseif(array_key_exists($term, $mapeamento_areas)){
                $event->terms['area'] = array($mapeamento_areas[$term]);
            }else{
                $event->terms['area'] = array('Outros');
            }

            $this->log->info("      >> >> EVENTO $event->name CRIADO");
            $event->save(true);

            if(strtolower($event_data['Horário']) === '24h'){
                $startsOn = '2014-05-17';
                $startsAt = '18:00';
                $duration = '24h00';
            }else{
                $startsOn =  $event_data['Data'] === '17/5' ? '2014-05-17' : '2014-05-18';
                $startsAt = $event_data['Horário'];
                $duration = '';
            }
            @list($hours, $minutes) = explode(':', $startsAt);

            $startsAt = str_pad($hours, 2, '0', STR_PAD_LEFT)  . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

            $eoccurrence = new \MapasCulturais\Entities\EventOccurrence();
            $eoccurrence->space = $space;
            $eoccurrence->event = $event;


            $rule = '{
                "spaceId":"' . $space->id . '",
                "startsAt": "' . $startsAt . '",
                "duration": "' . $duration . '",
                "frequency": "daily",
                "startsOn": "' . $startsOn . '",
                "until": "' . $startsOn . '"
            }';

            $eoccurrence->rule = json_decode($rule);
            $eoccurrence->save(true);
        };


        foreach($spaces as $space_data){
            $space = new \MapasCulturais\Entities\Space;
            $space->owner = $agent;
            $space->name = $space_data['Local/Palco'];
            $space->type = 501;
            $space->terms['area'] = array('Outros');

            if($space_data['Endereço'])
                $space->endereco = $space_data['Endereço'];

            if($space_data['Georreferenciamento']){
                $loc = array(0,0);

                if(preg_match('#^[ ]*(-\d{2}\.\d+)[ ]*,[ ]*(-\d{2}\.\d+)[ ]*#', $space_data['Georreferenciamento'], $match1))
                    $loc = array($match1[1], $match1[2]);

                else if(preg_match('#^[ ]*(-\d{2},\d+)[ ]*;[ ]*(-\d{2},\d+)[ ]*#', $space_data['Georreferenciamento'], $match2))
                    $loc = array(str_replace(',','.',$match2[1]), str_replace(',','.',$match2[2]));

                if(count($loc) == 2){
                    $space->location = array((float) $loc[1], (float) $loc[0]);
                }
            }
            $this->log->info("VIRADA>> ESPAÇO $space->name CRIADO  ($space->location)");
            $space->save(true);


            foreach($events as $event_data){
                if(str_replace(' ','',strtolower($event_data['Palco'])) != str_replace(' ','',strtolower($space_data['Local/Palco'])))
                    continue;
                $created_events[] = md5(json_encode($event_data));
                $create_event($space, $event_data);
            }
        }

        $this->log->info("EVENTOS NAO CRIADOS:::::");
        foreach($events as $event_data){
            $md5 = md5(json_encode($event_data));

            if(!in_array($md5, $created_events)){
                $create_event($space_errado, $event_data, $event_data['Palco']);
            }
        }
        return true;
    },

    'create-occurrence_id_seq' => function (){
        $app = \MapasCulturais\App::i();
        $em = $app->em;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $q = $em->createNativeQuery("
            CREATE SEQUENCE occurrence_id_seq
                START WITH 100000
                INCREMENT BY 1
                MINVALUE 100000
                NO MAXVALUE
                CACHE 1
                CYCLE;", $rsm);


        $q->execute();

        return true;
    },

    'remove agents and spaces with error - 2014-02-07' => function(){
        $spaces = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\Space e WHERE LOWER(TRIM(e.name)) LIKE 'erro%'")->getResult();
        $num_spaces = count($spaces);

        foreach ($spaces as $i => $s){
            $i++;
            $this->log->info("DB UPDATE > Removing space ({$i}/{$num_spaces}) \"{$s->name}\"");
            $s->delete();
        }

        $agents = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\Agent e WHERE LOWER(TRIM(e.name)) LIKE 'erro%'")->getResult();
        $num_agents = count($agents);

        foreach ($agents as $i => $a){
            $i++;
            $this->log->info("DB UPDATE > Removing agent ({$i}/{$num_agents}) \"{$a->name}\"");
            $a->destroy();
        }

        $users = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\User e WHERE SIZE(e.agents) = 0")->getResult();
        $num_users = count($users);
        $this->log->info("USUÁRIOS SEM AGENTES: $num_users");

        foreach ($users as $i => $u){
            $i++;
            $this->log->info("DB UPDATE > Removing user ({$i}/{$num_users}) \"{$u->id} ($u->email)\"");
            $u->delete();
        }

        return true;
    },

    '0001' => function(){
        $users = $this->repo('User')->findAll();
        foreach($users as $u){
            $profile = $u->getProfile();
            if(!$profile->isUserProfile){
                $this->log->info("DB UPDATE > Setting profile of the User \"{$u->id}\": Agent \"{$profile->name}\" ({$profile->id}).");
                $profile->setAsUserProfile();
            }
        }

        return true;
    }
);