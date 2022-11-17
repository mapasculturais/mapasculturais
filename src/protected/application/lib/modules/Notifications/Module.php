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

    public function sendMail($to, $msg, $subject = null) {
        $app = App::i();
        $dataValue = [
            'message'    => $msg
        ];
        $message = $app->renderMailerTemplate('request_relation', $dataValue);
        $subject = $subject == null ? $message['title'] : $subject;
        $mail = [
            'from'    => $app->config['mailer.from'],
            'to'      => $to,
            'subject' => $subject,
            'body'    => $message['body']
        ];
        $app->sendMailMessage($app->createMailMessage($mail));
    }
    
    function _init() {
         $app = App::i();
         $module = $this;
         /* === NOTIFICATIONS  === */
         
        // para todos os requests
        $app->hook('workflow(<<*>>).create', function() use($app, $module) {
            if ($this->notifications) {
                $app->disableAccessControl();
                foreach ($this->notifications as $n) {
                    $n->delete();
                }
                $app->enableAccessControl();
            }

            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;//registration
            $destination = $this->destination;
            // dump($origin);
            // dump($origin->opportunity);
            // dump($destination);
            
            $origin_type = strtolower($origin->entityTypeLabel);
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;
            $destination_type = strtolower($destination->entityTypeLabel);
            
            $profile_link = "<a rel='noopener noreferrer' href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a rel='noopener noreferrer' href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a rel='noopener noreferrer' href=\"{$origin_url}\">{$origin_name}</a>";

            /**
             * Para uso do relacionamento dos espaços
             * $nameSpace = $destination->name
             */
            
            $nameSpace = "<a href=\"{espaco/$destination->id}\" target='_blank'>{$destination->name}</a>";
            $nameProjectSpace = (isset($origin->opportunity)) ? "<a href=\"{oportunidade/$origin->opportunity->id}\" target='_blank'>{$origin->opportunity->name}</a>" : "";

            if (!is_null($destination->subsite)) {
                $url_destination_panel = $destination->subsite->url . '/painel/';
            } else {
                $url_destination_panel = $app->createUrl('panel');
            }

            $urlDestinationPanel_link = "<br> <a href=\"{$url_destination_panel}\"> Acesse aqui o seu painel </a>";

            $message_to_requester = '';

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->getClassName() === 'MapasCulturais\Entities\EvaluationMethodConfiguration'){
                        $opportunity = $origin->opportunity;
                        $prev = $opportunity;
                        
                        while($_prev = $prev->parent){
                            $prev = $_prev;
                        }
                        
                        if($prev->equals($opportunity)){
                            $opportunity_link = "<a href=\"{$opportunity->singleUrl}\">{$opportunity->name}</a>";
                        } else {
                            $opportunity_link = "<a href=\"{$opportunity->singleUrl}\">{$prev->name} &raquo; {$opportunity->name}</a>";
                        }
                        
                        $owner_entity = $prev->ownerEntity;
                        $owner_entity_label = strtolower($owner_entity->getEntityTypeLabel());
                        $owner_entity_link = "<a href=\"{$owner_entity->singleUrl}\">{$owner_entity->name}</a>";
                        
                        $subject = i::__("Requisição para avaliar oportunidade");
                        $message = sprintf(i::__("%s te convida para avaliar a oportunidade %s vinculada ao %s %s. %s"), $profile_link, $opportunity_link, $owner_entity_label, $owner_entity_link, $urlDestinationPanel_link);
                        $message_to_requester = sprintf(i::__("Seu convite para fazer do agente %s um avaliador foi enviada."), $destination_link);
                        
                    } else if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $project = $origin->project;
                        $opportunity = $origin->opportunity;

                        if ($project) {
                            $subject = i::__("Requisição para relacionar agente em uma inscrição");
                            $message = sprintf(i::__("%s quer relacionar o agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, $origin->number, "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                            $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s à inscrição %s no projeto %s foi enviada."), $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>");
                        } else if ($opportunity){
                            $subject = i::__("Requisição para relacionar agente em uma inscrição");
                            $message = sprintf(i::__("%s quer relacionar o agente %s à inscrição %s na oportunidade %s."), $profile_link, $destination_link, $origin->number, "<a href=\"{$opportunity->singleUrl}\">{$opportunity->name}</a>");
                            $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s à inscrição %s no oportunidade %s foi enviada."), $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$opportunity->singleUrl}\">{$opportunity->name}</a>");
                        } else {
                            $subject = i::__("Requisição para relacionar agente");
                            $message = sprintf(i::__("%s quer relacionar o agente %s ao %s %s."), $profile_link, $destination_link, $origin_type, $origin_link);
                            $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s ao %s %s foi enviada."), $destination_link, $origin_type, $origin_link);
                        }

                    } else {
                        $subject = i::__("Requisição para relacionar agente");
                        /* Translators: "{$profile_link} quer relacionar o agente {$destination_link} ao {$origin_type} {$origin_link}." */
                        $message = sprintf(i::__("%s quer relacionar o agente %s ao %s %s. %s"), $profile_link, $destination_link, $origin_type, $origin_link, $urlDestinationPanel_link);
                        /* Translators: "Sua requisição para relacionar o agente {$destination_link} ao {$origin_type} {$origin_link} foi enviada." */
                        $message_to_requester = sprintf(i::__("Sua requisição para relacionar o agente %s ao %s %s foi enviada."), $destination_link, $origin_type, $origin_link);
                    }
                    break;
                    
                case "MapasCulturais\Entities\RequestEntitiesTransference":
                    $subject = i::__("Requisição de transferência de entidades");
                    $message = sprintf(i::__("%s apagou sua conta e transferiu todas as suas entidades para seu agente %s"), $profile_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $subject = i::__("Requisição de mudança de propriedade");
                    $message = sprintf(i::__("%s está requisitando a mudança de propriedade do %s %s para o agente %s. %s"), $profile_link, $origin_type, $origin_link, $destination_link, $urlDestinationPanel_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para alterar a propriedade do %s %s para o agente %s foi enviada."), $origin_type, $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $subject = sprintf(i::__("Requisição para criação de um %s filho"), $origin_type);                    
                    $message = sprintf(i::__("%s quer que o %s %s seja um %s filho de %s. %s"), $profile_link, $origin_type, $origin_link, $origin_type, $destination_link, $urlDestinationPanel_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para fazer do %s %s um %s filho de %s foi enviada."), $origin_type, $origin_link, $origin_type, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $subject = i::__("Requisição para adicionar evento");
                    $message = sprintf(i::__("%s quer adicionar o evento %s que ocorre %s no espaço %s. %s"), $profile_link, $origin_link, "<em>{$this->rule->description}</em>", $destination_link, $urlDestinationPanel_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para criar a ocorrência do evento %s no espaço %s foi enviada."), $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $subject = i::__("Requisição para relacionar o evento ao projeto");
                    $message = sprintf(i::__("%s quer relacionar o evento %s ao projeto %s. %s"), $profile_link, $origin_link, $destination_link, $urlDestinationPanel_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para associar o evento %s ao projeto %s foi enviada."), $origin_link, $destination_link);
                    break;
                case "MapasCulturais\Entities\RequestSealRelation":
                    $subject = i::__("Requisição para relacionar selo");
                    $message = sprintf(i::__("%s quer relacionar o selo %s ao %s %s. %s"), $profile_link, $destination_link, $origin_type, $origin_link, $urlDestinationPanel_link);
                    $message_to_requester = sprintf(i::__("Sua requisição para relacionar o selo %s ao %s %s foi enviada."), $destination_link, $origin_type, $origin_link);
                    break;
                //ESTÁ DANDO ERRO PARA NOTIFICAÇÃO
                case "MapasCulturais\Entities\RequestSpaceRelation":
                    
                    if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $opportunity = $origin->opportunity;
                        $prev = $opportunity;
                        
                        while($_prev = $prev->parent){
                            $prev = $_prev;
                        }
                        
                        $opportunity_link = "<a href=\"{$opportunity->singleUrl}\">{$opportunity->name}</a>";
                        $subject = i::__("Requisição para relacionar o espaço ao projeto");
                        $message = sprintf(i::__("%s quer relacionar o espaço %s à inscrição %s no projeto %s."), $profile_link, $destination_link, $origin_link, $opportunity_link);
                        $message_to_requester = sprintf(i::__("Sua requisição para relacionar o espaço %s à inscrição %s no projeto %s foi enviada."), $destination_link, $origin_link, $opportunity_link); 
                    }
                    break;
                default:
                    $subject = null;
                    $message = $message_to_requester = "REQUISIÇÃO - NÃO DEVE ENTRAR AQUI";
                    break;
            }

            if($message_to_requester){
                // message to requester user
                $notification = new Notification;
                $notification->user = $requester;
                $notification->message = $message_to_requester;
                $notification->request = $this;
                $notification->save(true);
            }

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
                $module->sendMail($user->email, $message, $subject);
            }

            if (!$requester->equals($origin->ownerUser) && !in_array($origin->ownerUser->id, $notified_user_ids)) {
                $notification = new Notification;
                $notification->user = $origin->ownerUser;
                $notification->message = $message;
                $notification->request = $this;
                $notification->save(true);
                $module->sendMail($origin->ownerUser->email, $message, $subject);
            }
        });

        $app->hook('workflow(<<*>>).approve:before', function() use($app) {
            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityTypeLabel);
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
                        $message = sprintf(i::__("%s aceitou o relacionamento do agente %s à inscrição %s no projeto %s."), $profile_link, $destination_link, "<a href=\"{$origin->singleUrl}\" >{$origin->number}</a>", "<a href=\"{$origin->opportunity->singleUrl}\">{$origin->opportunity->name}</a>");
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

            $origin_type = strtolower($origin->entityTypeLabel);
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
