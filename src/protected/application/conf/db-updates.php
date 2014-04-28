<?php
use MapasCulturais\App;

return array(
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