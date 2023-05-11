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

            if ($onlyAdmins) {
                $dest = 'to';
            } else {
                $dest = 'bcc';
            }

            foreach($_subsite_admins as $user) {
                $destinatarios[$dest][] = $user->email;
            }

            // Usado para enviar para entidades do contato, e e-mails do responsavel tambem
            if (!$onlyAdmins) {
                $_other_recipients = $this->getEntityAndResponsibleEmails($_app,$_entity,true);

                $first_valid_mail = array_shift($_other_recipients);
                $mail_validator = new Email();

                if (!is_null($first_valid_mail) && $mail_validator->validate($first_valid_mail)) {
                    $destinatarios['to'][] = $first_valid_mail;
                } else {
                    $destinatarios['to'][] = array_shift($destinatarios[$dest]);
                }

                foreach ($_other_recipients as $_recipient) {
                    if ($mail_validator->validate($_recipient) && !in_array($_recipient, $destinatarios)) {
                        $destinatarios['bcc'][] = $_recipient;
                    }
                }
            }   

            return $destinatarios;
        }

        return array();
    }

    private function getEntityAndResponsibleEmails($app, $_entity, $filter = false) {
        if ($app instanceof \MapasCulturais\App && $_entity instanceof \MapasCulturais\Entity) {
            $_responsible = $app->repo('Agent')->find($_entity->owner->id);
            $app->disableAccessControl();
            $emails = [
                'entity_public'       => $_entity->emailPublico,
                'entity_private'      => $_entity->emailPrivado,
                'responsible_public'  => $_responsible->emailPublico,
                'responsible_private' => $_responsible->emailPrivado
            ];
            $app->enableAccessControl();

            if ($filter) {
               $emails = array_filter($emails, function($mail) {
                  return !is_null($mail);
               });
               $emails = array_unique($emails);
            }

            return $emails;
        }

        return array();
    }

    public function _init() {
        $app = App::i();
        $config = $this->_config;

        $app->view->enqueueScript('app', 'recaptcha', 'https://www.google.com/recaptcha/api.js');

        $plugin = $this;

        $params = [];

        if(array_key_exists('compliant',$this->_config)) {
            $params['compliant'] = $this->_config['compliant'];
        }

        if(array_key_exists('suggestion',$this->_config)) {
            $params['suggestion'] = $this->_config['suggestion'];
        }
              
        if(array_key_exists('app.recaptcha.key',$app->_config)) {
            $params['googleRecaptchaSiteKey'] = $app->_config['app.recaptcha.key'];
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
                    'recaptchaRequired' => i::__('Recaptcha não selecionado ou inválido, tente novamente.'),
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
            
             //Verificando recaptcha v2
            if (!$plugin->verifyRecaptcha2()) {
                throw new \Exception(\MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.'));
            }

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
                'name'          => $app->user->is('guest') ? \MapasCulturais\i::__("Usuário Guest") : $entity->owner->name,
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
                $destinatarios = $plugin->setRecipients($app, $entity, true);
                $tos = $destinatarios['to'];
                
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

            //Verificando recaptcha v2
            if (!$plugin->verifyRecaptcha2()) {
                throw new \Exception( \MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.') );
            }

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
                'name'          => $app->user->is('guest') ? \MapasCulturais\i::__("Usuário Guest") : $entity->owner->name,
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

                if (array_key_exists('only_owner',$this->data)) {
                    $suggestion_mail = ['from' => $app->config['mailer.from'], 'subject' => $message['title'], 'body' => $message['body']];

                    if ($this->data['only_owner'] === "true") {
                        $tos = array_values($plugin->getEntityAndResponsibleEmails($app,$entity,true));
                        $app->applyHook('mapasculturais.suggestionMessage.destination_to', [&$tos]);

                        $suggestion_mail['to'] = $tos;
                    } else {
                        $destinatarios = $plugin->setRecipients($app, $entity);
                        $tos = $destinatarios['to'];
                        $app->applyHook('mapasculturais.suggestionMessage.destination_to', [&$tos]);

                        $bccs = $destinatarios['bcc'];
                        $app->applyHook('mapasculturais.suggestionMessage.destination_bcc', [&$bccs]);

                        $suggestion_mail['to'] = $tos;
                        $suggestion_mail['bcc'] = $bccs;
                    }

                    $app->createAndSendMailMessage($suggestion_mail);
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

    public function verificarToken($token, $secretkey)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $data = [
            "secret" => $secretkey,
            "response" => $token,
        ];

        $options = [
            "http" => [
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($data), 
            ],
        ];

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $result = json_decode($result);

        return $result->success;
    }

    public function verifyRecaptcha2() {
        $app = App::i();
        $config = $app->_config;
    
        if (!isset($app->_config['app.recaptcha.key'])) return true;
        if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) return false;

        $token = $_POST["g-recaptcha-response"];
        return $this->verificarToken($token, $app->_config['app.recaptcha.secret']);
    }
}
