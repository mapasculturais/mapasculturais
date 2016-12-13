<?php 
 use MapasCulturais\Entities;
 use MapasCulturais\Entities\Notification;
 $app = MapasCulturais\App::i();
 /* === NOTIFICATIONS  === */
        // para todos os requests
        $app->hook('workflow(<<*>>).create', function() use($app) {

            if ($this->notifications) {
                $app->disableAccessControl();
                foreach ($this->notifications as $n) {
                    $n->delete();
                }
                $app->enableAccessControl();
            }

            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityTypeLabel());
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;
            $destination_type = strtolower($destination->entityTypeLabel());

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $message = "{$profile_link} quer relacionar o agente {$destination_link} à inscrição {$origin->number} no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        $message_to_requester = "Sua requisição para relacionar o agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a> foi enviada.";
                    }else{
                        $message = "{$profile_link} quer relacionar o agente {$destination_link} ao {$origin_type} {$origin_link}.";
                        $message_to_requester = "Sua requisição para relacionar o agente {$destination_link} ao {$origin_type} {$origin_link} foi enviada.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} está requisitando a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    $message_to_requester = "Sua requisição para alterar a propriedade do {$origin_type} {$origin_link} para o agente {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} quer que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    ;
                    $message_to_requester = "Sua requisição para fazer do {$origin_type} {$origin_link} um {$origin_type} filho de {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} quer adicionar o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    $message_to_requester = "Sua requisição para criar a ocorrência do evento {$origin_link} no espaço {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} quer relacionar o evento {$origin_link} ao projeto {$destination_link}.";
                    $message_to_requester = "Sua requisição para associar o evento {$origin_link} ao projeto {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestSealRelation":
                    $message = "{$profile_link} quer relacionar o selo {$destination_link} ao {$origin_type} {$origin_link}.";
                    $message_to_requester = "Sua requisição para relacionar o selo {$destination_link} ao {$origin_type} {$origin_link} foi enviada.";
                    break;
                default:
                    $message = $message_to_requester = "REQUISIÇÃO - NÃO DEVE ENTRAR AQUI";
                    break;
            }

            // message to requester user
            $notification = new Notification;
            $notification->user = $requester;
            $notification->message = $message_to_requester;
            $notification->request = $this;
            $notification->save(true);

            $notified_user_ids = array($requester->id);


            foreach ($destination->usersWithControl as $user) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($user->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $user->id;

                $notification = new Notification;
                $notification->user = $user;
                $notification->message = $message;
                $notification->request = $this;
                $notification->save(true);
            }

            if (!$requester->equals($origin->ownerUser) && !in_array($origin->ownerUser->id, $notified_user_ids)) {
                $notification = new Notification;
                $notification->user = $origin->ownerUser;
                $notification->message = $message;
                $notification->request = $this;
                $notification->save(true);
            }
        });

        $app->hook('workflow(<<*>>).approve:before', function() use($app) {
            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityTypeLabel());
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $message = "{$profile_link} aceitou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                    }else{
                        $message = "{$profile_link} aceitou o relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} aceitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} aceitou que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} aceitou adicionar o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} aceitou relacionar o evento {$origin_link} ao projeto {$destination_link}.";
                    break;
                default:
                    $message = "A requisição foi aprovada.";
                    break;
            }

            $users = array();

            // notifica quem fez a requisição
            $users[] = $this->requesterUser;

            if ($this->getClassName() === "MapasCulturais\Entities\RequestChangeOwnership" && $this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                // se não foi o dono da entidade de destino que fez a requisição, notifica o dono
                if (!$destination->ownerUser->equals($this->requesterUser))
                    $users[] = $destination->ownerUser;

                // se não é o dono da entidade de origem que está aprovando, notifica o dono
                if (!$origin->ownerUser->equals($app->user))
                    $users[] = $origin->ownerUser;
            }else {
                // se não foi o dono da entidade de origem que fez a requisição, notifica o dono
                if (!$origin->ownerUser->equals($this->requesterUser))
                    $users[] = $origin->ownerUser;

                // se não é o dono da entidade de destino que está aprovando, notifica o dono
                if (!$destination->ownerUser->equals($app->user))
                    $users[] = $destination->ownerUser;
            }

            $notified_user_ids = array();

            foreach ($users as $u) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($u->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $u->id;

                $notification = new Notification;
                $notification->message = $message;
                $notification->user = $u;
                $notification->save(true);
            }
        });


        $app->hook('workflow(<<*>>).reject:before', function() use($app) {
            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityTypeLabel());
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->canUser('@control')){
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = "{$profile_link} cancelou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} cancelou o pedido de relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                        }
                    }else{
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = "{$profile_link} rejeitou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} rejeitou o relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                        }
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    if ($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelou o pedido de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}." :
                                "{$profile_link} rejeitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    } else {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelou o pedido de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}." :
                                "{$profile_link} rejeitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido para que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}." :
                            "{$profile_link} rejeitou que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido de autorização do evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}." :
                            "{$profile_link} rejeitou o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido de relacionamento do evento {$origin_link} ao projeto {$destination_link}." :
                            "{$profile_link} rejeitou o relacionamento do evento {$origin_link} ao projeto {$destination_link}.";
                    break;
                default:
                    $message = $origin->canUser('@control') ?
                            "A requisição foi cancelada." :
                            "A requisição foi rejeitada.";
                    break;
            }

            $users = array();

            if (!$app->user->equals($this->requesterUser)) {
                // notifica quem fez a requisição
                $users[] = $this->requesterUser;
            }

            if ($this->getClassName() === "MapasCulturais\Entities\RequestChangeOwnership" && $this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                // se não foi o dono da entidade de destino que fez a requisição, notifica o dono
                if (!$destination->ownerUser->equals($this->requesterUser))
                    $users[] = $destination->ownerUser;

                // se não é o dono da entidade de origem que está rejeitando, notifica o dono
                if (!$origin->ownerUser->equals($app->user))
                    $users[] = $origin->ownerUser;
            }else {
                // se não foi o dono da entidade de origem que fez a requisição, notifica o dono
                if (!$origin->ownerUser->equals($this->requesterUser))
                    $users[] = $origin->ownerUser;

                // se não é o dono da entidade de destino que está rejeitando, notifica o dono
                if (!$destination->ownerUser->equals($app->user))
                    $users[] = $destination->ownerUser;
            }

            $notified_user_ids = array();

            foreach ($users as $u) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($u->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $u->id;

                $notification = new Notification;
                $notification->message = $message;
                $notification->user = $u;
                $notification->save(true);
            }
        });


        /* ---------------------- */
