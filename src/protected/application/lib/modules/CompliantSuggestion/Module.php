<?php

namespace CompliantSuggestion;

use MapasCulturais\App,
    MapasCulturais\i,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions,
    Respect\Validation\Rules\Email;

class Module extends \MapasCulturais\Module {

    public function __construct(array $config = array()) {
        $config = $config + ['compliant' => true, 'suggestion' => true];

        parent::__construct($config);
    }

    private function setRecipients($_app, $_entity, $onlyAdmins = false) {

        if ($_app instanceof \MapasCulturais\App && $_entity instanceof \MapasCulturais\Entity) {
            $_subsite_admins = $_app->repo('User')->getAdmins($_entity->subsiteId);
            $destinatarios = array();

            foreach($_subsite_admins as $user) {
                $destinatarios[] = $user->email;
            }

            if (!$onlyAdmins) {
                $_responsible = $_app->repo('Agent')->find($_entity->owner->id);
                $_app->disableAccessControl();
                $_other_recipients = [
                    'entity_public'       => $_entity->emailPublico,
                    'entity_private'      => $_entity->emailPrivado,
                    'responsible_public'  => $_responsible->emailPublico,
                    'responsible_private' => $_responsible->emailPrivado
                ];
                $_app->enableAccessControl();

                $mail_validator = new Email();
                foreach ($_other_recipients as $_recipient) {
                    if ($mail_validator->validate($_recipient) && !in_array($_recipient, $destinatarios)) {
                        $destinatarios[] = $_recipient;
                    }
                }
            }   

            return $destinatarios;
        }

        return array();
    }

    public function _init() {
        $app = App::i();

        $plugin = $this;

        $params = [];

        if(array_key_exists('compliant',$this->_config)) {
            $params['compliant'] = $this->_config['compliant'];
        }

        if(array_key_exists('suggestion',$this->_config)) {
            $params['suggestion'] = $this->_config['suggestion'];
        }

        $app->hook('template(<<agent|space|event|project>>.<<single>>.main-content):end', function() use ($app, $plugin, $params) {
            $this->part('compliant_suggestion.php',$params);
        });

        $app->hook('mapasculturais.head', function() use($app, $plugin){
            $entity = $app->view->controller->requestedEntity;

            if($entity){
                $app->view->jsObject['angularAppDependencies'][] = 'module.compliantSuggestion';

                $app->view->enqueueScript('app', 'module-compliantSuggestion', 'js/ng.modules.compliantSuggestion.js');
                $app->view->localizeScript('compliantSuggestion', [
                    'compliantEmailRequired' => i::__('O preenchimento do e-mail é obrigatório.'),
                    'compliantTypeRequired' => i::__('O preenchimento do tipo de denúncia é obrigatório.'),
                    'compliantMessageRequired' => i::__('O preenchimento da mensagem da denúncia é obrigatório.'),
                    'compliantSent' => i::__('A denúncia foi enviada.'),

                    'suggestionEmailRequired' => i::__('O preenchimento do e-mail é obrigatório.'),
                    'suggestionTypeRequired' => i::__('O preenchimento do tipo de sugestão é obrigatório.'),
                    'suggestionMessageRequired' => i::__('O preenchimento da mensagem é obrigatório.'),
                    'suggestionSent' => i::__('A sugestão foi enviada.'),

                    'error' => i::__('Erro inesperado ao o enviar a mensagem')
                ]);
            }
        });


        $app->hook('POST(<<agent|space|event|project>>.sendCompliantMessage)', function() use ($plugin) {
            $app = App::i();
            $entity = $app->repo($this->entityClassName)->find($this->data['entityId']);
            if(array_key_exists('anonimous',$this->data) && $this->data['anonimous']) {
                $person = \MapasCulturais\i::__("Anônimo");
                $anonimous = \MapasCulturais\i::__("Anônima");
                $person_email = \MapasCulturais\i::__("Anônimo");
            } else {
                $person = $this->data['name'];
                $anonimous = "";
                $person_email = $this->data['email'];
            }

            $dataValue = [
                'name'          => $app->user->is('guest') ? \MapasCulturais\i::__("Usuário Guest") : $app->user->profile->name,
                'entityType'    => $entity->getEntityTypeLabel(),
                'entityName'    => $entity->name,
                'person'        => $person,
                'email'         => $person_email,
                'url'           => $entity->singleUrl,
                'type'          => $this->data['type'],
                'date'          => date('d/m/Y H:i:s',$_SERVER['REQUEST_TIME']),
                'message'       => $this->data['message']
            ];

            $message = $app->renderMailerTemplate('compliant',$dataValue);

            if(array_key_exists('mailer.from',$app->config) && !empty(trim($app->config['mailer.from']))) {
                $tos = $plugin->setRecipients($app, $entity, true);
                
                /**
                * @hook {ALL} 'mapasculturais.complaintMessage.destination' Destinátarios e-mail de denúncia 
                * @hookDescription permitir alterar os destinatários do email enviado pelo formulário de denúncia.
                * @hookGroup HookEmail
                * 
                * Envia e-mail para o administrador para instalação Mapas
                */
                $app->applyHook('mapasculturais.complaintMessage.destination', [&$tos]);
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $tos,
                    'subject' => $message['title'],
                    'body' => $message['body']
                ]);
            }
            if(array_key_exists('copy',$this->data) && $this->data['copy']) {
                if(array_key_exists('email',$this->data) && !empty(trim($this->data['email']))) {
                    $email = $this->data['email'];
                } else {
                    $email = $app->user->email;
                }

                if($email) {
                    /*
                    * Envia e-mail de cópia para o remetente da denúncia
                    */
                    $app->applyHook('mapasculturais.complaintMessage.destination', [&$email]);
                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $email,
                        'subject' => $message['title'],
                        'body' => $message['body']
                    ]);
                }
            }
        });

        $app->hook('POST(<<agent|space|event|project>>.sendSuggestionMessage)', function() use ($plugin) {
            $app = App::i();

            $entity = $app->repo($this->entityClassName)->find($this->data['entityId']);
            $message = "";
            if(array_key_exists('anonimous',$this->data) && $this->data['anonimous']) {
                $person = \MapasCulturais\i::__("Anônimo");
                $anonimous = \MapasCulturais\i::__("Anônima");
                $person_email = \MapasCulturais\i::__("Anônimo");
            } else {
                $person = $this->data['name'];
                $anonimous = "";
                $person_email = $this->data['email'];
            }

            $dataValue = [
                'name'          => $app->user->is('guest') ? \MapasCulturais\i::__("Usuário Guest") : $app->user->profile->name,
                'entityType'    => $entity->getEntityTypeLabel(),
                'entityName'    => $entity->name,
                'person'        => $person,
                'email'         => $person_email,
                'url'           => $entity->singleUrl,
                'type'          => $this->data['type'],
                'date'          => date('d/m/Y H:i:s',$_SERVER['REQUEST_TIME']),
                'message'       => $this->data['message']
            ];

            $message = $app->renderMailerTemplate('suggestion',$dataValue);
            if (array_key_exists('mailer.from',$app->config) && !empty(trim($app->config['mailer.from']))) {
                if (array_key_exists('only_owner',$this->data) && !$this->data['only_owner']) {
                    $tos = $plugin->setRecipients($app, $entity);
                    $app->applyHook('mapasculturais.suggestionMessage.destination', [&$tos]);
                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $tos,
                        'subject' => $message['title'],
                        'body' => $message['body']
                    ]);
                }

                if(isset($agent->user->email) && !empty($agent->user->email)) {
                    if(in_array('anonimous',$this->data) && !$this->data['anonimous']) {
                        $email = "<Anonimous>";
                    } else {
                        $email = $agent->user->email;
                    }
                    /**
                    * @hook {ALL} 'mapasculturais.suggestionMessage.destination' Destinátarios email de contato 
                    * @hookDescription permitir alterar os destinatários do email enviado pelo formulário de contato.
                    * @hookGroup HookEmail
                    *
                    * Envio de E-mail ao responsável da entidade
                    */
                    $app->applyHook('mapasculturais.suggestionMessage.destination', [&$email]);
                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $email,
                        'subject' => $message['title'],
                        'body' => $message['body']
                    ]);
                }
            }

            if(array_key_exists('copy',$this->data) && $this->data['copy']) {
                if(array_key_exists('email',$this->data) && !empty(trim($this->data['email']))) {
                    $email = $this->data['email'];
                } else {
                    $email = $app->user->email;
                }

                if($email) {
                    /*
                    * Envia e-mail de cópia para o remetente da denúncia
                    */
                    $app->applyHook('mapasculturais.suggestionMessage.destination', [&$email]);
                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $email,
                        'subject' => $message['title'],
                        'body' => $message['body']
                    ]);
                }
            }
        });
    }

    public function register() { }
}
