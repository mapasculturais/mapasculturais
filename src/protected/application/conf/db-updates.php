<?php
use MapasCulturais\App;

return array(
    'importa programação virada cultural 2014' => function(){
        return false;



        $area_definition = App::i()->getRegisteredTaxonomy('MapasCulturais\Entities\Event', 'area');

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

        $mapeamento_areas = array(
            'Exposições' => array('Artes Visuais'),
            'Cinema e Vídeo' => array('Audiovisual', 'Cinema')
        );

        $agent_id = 428;
        $project_id = 4;

        $agent = App::i()->repo('Agent')->find($agent_id);
        $project = App::i()->repo('Project')->find($project_id);

        $spaces = $csv_to_array(realpath(__DIR__ . '/../../../../tmp/virada-espacos.csv'));
        $events = $csv_to_array(realpath(__DIR__ . '/../../../../tmp/virada-eventos.csv'));
        var_dump($spaces, $events);
        die;
        $created_events = array();

        foreach($spaces as $space_data){
            $space = new \MapasCulturais\Entities\Space;
            $space->owner = $agent;
            $space->name = $space_data['Local/Palco'];
            $space->type = 501;
            $space->terms['area'] = array('Outros');

            if($space_data['Endereço'])
                $space->endereco = $space_data['Endereço'];

            if($space_data['Georreferenciamento']){
                $loc = explode(';', $space_data['Georreferenciamento']);
                if(count($loc) == 2){
                    $loc[0] = (float) str_replace(',','.',$loc[0]);
                    $loc[1] = (float) str_replace(',','.',$loc[0]);

                    $space->location = $loc;
                }
            }

            $space->save(true);

            foreach($events as $event_data){
                if($event_data['Palco'] != $space_data['Local/Palco'])
                    continue;

                foreach($event_data as $k => $v)
                    $event_data[$k] = trim($v);

                $created_events[] = $event_data;

                $event = new \MapasCulturais\Entities\Event;
                $event->owner = $agent;
                $event->project = $project;

                $event->name = $event_data['Atração'];
                $event->shortDescription = $event_data['Sinopse (até 350 caracteres)'];
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

                $event->save(true);

                $startsOn = (intval($edata->hour) >= 18) ? '2014-05-17' : '2014-05-18';

                $eoccurrence = new \MapasCulturais\Entities\EventOccurrence();
                $eoccurrence->space = $space;
                $eoccurrence->event = $event;


                $rule = '{
                    "spaceId":"' . $space->id . '",
                    "startsAt": "' . $edata->hour . '",
                    "endsAt": "' . $edata->hour . '",
                    "frequency": "daily",
                    "startsOn": "' . $startsOn . '",
                    "until": "' . $startsOn . '"
                }';
                $app->log->info(print_r($rule,true));
                $eoccurrence->rule = json_decode($rule);
                $eoccurrence->save(true);
            }
        }
        die;
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