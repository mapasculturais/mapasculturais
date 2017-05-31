<?php 
namespace Notifications;

use MapasCulturais\App,
    MapasCulturais\i,
    MapasCulturais\Entities,
    MapasCulturais\Entities\Notification;

class Module extends \MapasCulturais\Module{
    
    public function register() {
        ;
    }
    
    function _init() {
         $app = App::i();
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
                        $message = sprintf(i::__("%s quer relacionar o agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, $origin->number, "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                        $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s à inscrição %s no projeto %s foi enviada."), $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                    }else{
                        /* Translators: "{$profile_link} quer relacionar o agente {$destination_link} ao {$origin_type} {$origin_link}." */
                        $message = sprintf(i::__("%s quer relacionar o agente %s ao %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                        /* Translators: "Sua requisição para relacionar o agente {$destination_link} ao {$origin_type} {$origin_link} foi enviada." */
                        $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s ao %s %s foi enviada."), $destination_link, $origin_type, $origin_link);
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = sprintf(i::__("%s está requisitando a mudança de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para alterar a propriedade do %s %s para o agente %s foi enviada."), $origin_type, $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = sprintf(i::__("%s quer que o %s %s seja um %s filho de %s."), $profile_link, $origin_type, $origin_link, $origin_type, $destination_link);
                    ;
                    $message_to_requester = sprintf(i::__("Sua requisição para fazer do %s %s um %s filho de %s foi enviada."), $origin_type, $origin_link, $origin_type, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = sprintf(i::__("%s quer adicionar o evento %s que ocorre %s no espaço %s."), $profile_link, $origin_link, "<em>{$this->rule->description}</em>", $destination_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para criar a ocorrência do evento %s no espaço %s foi enviada."), $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = sprintf(i::__("%s quer relacionar o evento %s ao projeto %s."), $profile_link, $origin_link, $destination_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para associar o evento %s ao projeto %s foi enviada."), $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestSealRelation":
                    $message = sprintf(i::__("%s quer relacionar o selo %s ao %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para relacionar o selo %s ao %s %s foi enviada."), $destination_link, $origin_type, $origin_link);
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
                        $message = sprintf(i::__("%s aceitou o relacionamento do agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                    }else{
                        $message = sprintf(i::__("%s aceitou o relacionamento do agente %s com o %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = sprintf(i::__("%s aceitou a mudança de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = sprintf(i::__("%s aceitou que o %s %s seja um %s filho de %s."), $profile_link, $origin_type, $origin_link, $origin_type, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = sprintf(i::__("%s aceitou adicionar o evento %s que ocorre %s no espaço %s."), $profile_link, $origin_link, "<em>{$this->rule->description}</em>", $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = sprintf(i::__("%s aceitou relacionar o evento %s ao projeto %s."), $profile_link, $origin_link, $destination_link);
                    break;
                default:
                    $message = i::__("A requisição foi aprovada.");
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
                            $message = sprintf(i::__("%s cancelou o relacionamento do agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                        }else{
                            $message = sprintf(i::__("%s cancelou o pedido de relacionamento do agente %s com o %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                        }
                    }else{
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = sprintf(i::__("%s rejeitou o relacionamento do agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                        }else{
                            $message = sprintf(i::__("%s rejeitou o relacionamento do agente %s com o %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                        }
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    if ($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                        $message = $this->requesterUser->equals($requester) ?
                                sprintf(i::__("%s cancelou o pedido de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link) :
                                sprintf(i::__("%s rejeitou a mudança de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link);
                    } else {
                        $message = $this->requesterUser->equals($requester) ?
                                sprintf(i::__("%s cancelou o pedido de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link) :
                                sprintf(i::__("%s rejeitou a mudança de propriedade do %s %s para o agente %s."), $profile_link, $origin_type, $origin_link, $destination_link);
                    }
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = $origin->canUser('@control') ?
                            sprintf(i::__("%s cancelou o pedido para que o %s %s seja um %s filho de %s."), $profile_link, $origin_type, $origin_link, $origin_type, $destination_link):
                            sprintf(i::__("%s rejeitou que o %s %s seja um %s filho de %s."), $profile_link, $origin_type, $origin_link, $origin_type, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = $origin->canUser('@control') ?
                            sprintf(i::__("%s cancelou o pedido de autorização do evento %s que ocorre %s no espaço %s."), $profile_link, $origin_link, "<em>{$this->rule->description}</em>", $destination_link) :
                            sprintf(i::__("%s rejeitou o evento %s que ocorre %s no espaço %s."), $profile_link, $origin_link, "<em>{$this->rule->description}</em>", $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = $origin->canUser('@control') ?
                            sprintf(i::__("%s cancelou o pedido de relacionamento do evento %s ao projeto %s."), $profile_link, $origin_link, $destination_link) :
                            sprintf(i::__("%s rejeitou o relacionamento do evento %s ao projeto %s."), $profile_link, $origin_link, $destination_link);
                    break;
                default:
                    $message = $origin->canUser('@control') ?
                            i::__("A requisição foi cancelada."):
                            i::__("A requisição foi rejeitada.");
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
    }
}