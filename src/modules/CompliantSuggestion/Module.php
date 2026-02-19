<?php

namespace CompliantSuggestion;

use MapasCulturais\App,
    MapasCulturais\i,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions,
    Respect\Validation\Rules\Email;

class Module extends \MapasCulturais\Module {

    /**
     * Config opcional (ex.: app config 'module.CompliantSuggestion', env):
     * - complaint.to: array de e-mails. Se preenchido, To = lista + saasSuperAdmins; vazio = todos os admins (atual).
     * - complaint.bcc: array de e-mails. Se preenchido, BCC = lista; vazio = sem BCC (atual).
     * - suggestion.to: array de e-mails. Se preenchido, To = lista + responsável (se válido); vazio = só responsável ou saasSuperAdmin.
     * - suggestion.bcc: false = BCC desligado; null = atual (todos admins); array = lista fixa.
     */
    public function __construct(array $config = array()) {
        $config += [
            'compliant' => true,
            'suggestion' => true,
            'complaint.to' => [],
            'complaint.bcc' => [],
            'suggestion.to' => [],
            'suggestion.bcc' => null,
        ];

        parent::__construct($config);
    }

    /**
     * Retorna e-mails dos usuários com role saasSuperAdmin.
     * getByRole() pode retornar array de [Role, User, Agent] ou array de Role (conforme Doctrine).
     */
    private function getSaasSuperAdminEmails(\MapasCulturais\App $app) {
        $emails = [];
        $mail_validator = new Email();
        if($result = $app->repo('User')->getByRole('saasSuperAdmin', 0)){
        foreach ($result as $row) {
                if ($row->user->email && $mail_validator->validate($row->user->email)) {
                    $emails[] = $row->user->email;
                }
            }
        }
        return array_values(array_unique(array_filter($emails)));
    }

    /**
     * Ordem de busca para responsável: user do dono, entidade privado, entidade público, agente responsável privado, público.
     * Para Agent, o dono direto é o User vinculado (user_id); para as demais entidades, entity->owner (Agent)->user.
     */
    private function getResponsibleEmailsInOrder(\MapasCulturais\App $app, \MapasCulturais\Entity $entity) {
        $app->disableAccessControl();
        $list = [];

        if ($entity->owner->emailPrivado) {
            $list[] = $entity->owner->emailPrivado;
        }
        if ($entity->owner->emailPublico) {
            $list[] = $entity->owner->emailPublico;
        }

        if ($entity->owner->user->email) {
            $list[] = $entity->owner->user->email;
        }
        $app->enableAccessControl();
        return array_values(array_unique(array_filter($list)));
    }

    /**
     * Destinatários da denúncia conforme config: complaint.to, complaint.bcc.
     */
    public function getComplaintRecipients(\MapasCulturais\App $app, \MapasCulturais\Entity $entity) {
        $config = $app->config['module.CompliantSuggestion'];
        $tos = $this->getSaasSuperAdminEmails($app);
        $bccs = [];

        
        $listTo = isset($config['complaint.to']) ? (array) $config['complaint.to'] : [];
        $listBcc = isset($config['complaint.bcc']) ? (array) $config['complaint.bcc'] : [];

        if (!empty($listTo)) {
            $tos = array_merge($tos, $listTo);
            $saas = $this->getSaasSuperAdminEmails($app);
            $tos = array_values(array_unique(array_merge($tos, $saas)));
        } 
        
        if (!empty($listBcc)) {
            $bccs = array_values(array_unique(array_filter($listBcc)));
        }
        
        $tos = array_values(array_unique(array_filter($tos)));

        return ['to' => $tos, 'bcc' => $bccs];
    }

    /**
     * Destinatários do contato/sugestão conforme config: suggestion.to, suggestion.bcc.
     * To: responsável (ordem getResponsibleEmailsInOrder) ou um saasSuperAdmin; se houver lista na config, lista + responsável válido.
     * BCC: off = ninguém; null = comportamento atual (todos admins); array = lista fixa.
     */
    private function getSuggestionRecipients(\MapasCulturais\App $app, \MapasCulturais\Entity $entity) {
        $config = $app->config['module.CompliantSuggestion'];
        $suggestion_bcc = array_key_exists('suggestion.bcc', $config) ? $config['suggestion.bcc'] : null;

        $mail_validator = new Email();
        $responsibleList = $this->getResponsibleEmailsInOrder($app, $entity);
        $tos = [];
        foreach ($responsibleList as $email) {
            if ($mail_validator->validate($email)) {
                $tos[] = $email;
            }
        }

        if($suggestion_bcc === "off") { // desligado
            $bccs = [];
        } else if(is_null($suggestion_bcc)) { // comportamento atual (todos admins)
            $_subsite_admins = $app->repo('User')->getAdmins($entity->subsiteId);
            foreach ($_subsite_admins as $user) {
                $bccs[] = $user->email;
            }
        } else if(is_array($suggestion_bcc) && $suggestion_bcc) { // lista fixa
            $bccs = array_values(array_unique(array_filter($suggestion_bcc)));
        }

        return ['to' => $tos, 'bcc' => $bccs];
    }

    public function _init() {
        $app = App::i();
        $config = $this->_config;


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
            $plugin->addConfigToJs();

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


        $app->hook('POST(<<agent|space|event|project|opportunity>>.sendComplaintMessage)', function() use ($plugin) {
            $app = App::i();

            // Se não recebemos o token, não há motivo para avançar para a verificação
            if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
                throw new \Exception(\MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.'));
            }

            //Verificando recaptcha v2
            if (!$app->verifyCaptcha($_POST['g-recaptcha-response'])) {
                throw new \Exception(\MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.'));
            }

            $entity = $app->repo($this->entityClassName)->find($this->data['entityId']);
            if(array_key_exists('anonimous',$this->data) && $this->data['anonimous']) {
                $person = \MapasCulturais\i::__("Anônimo");
                $person_email =  \MapasCulturais\i::__("Anônimo");
            } else {
                $person = $this->data['name'];
                $person_email = $this->data['email'];
            }

            $dataValue = [
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
                $destinatarios = $plugin->getComplaintRecipients($app, $entity);

                $tos = $destinatarios['to'];
                $bccs = $destinatarios['bcc'];
                

                /**
                * @hook {ALL} 'mapasculturais.complaintMessage.destination' Destinátarios e-mail de denúncia
                * @hookDescription permitir alterar os destinatários do email enviado pelo formulário de denúncia.
                * @hookGroup HookEmail
                *
                * Envia e-mail para o administrador para instalação Mapas
                */
                $app->applyHook('mapasculturais.complaintMessage.destination', [&$tos]);
                $app->applyHook('mapasculturais.complaintMessage.destination_bcc', [&$bccs]);

                $mailData = [
                    'from' => $app->config['mailer.from'],
                    'to' => $tos,
                    'subject' => $message['title'],
                    'body' => $message['body']
                ];
                if (!empty($bccs)) {
                    $mailData['bcc'] = $bccs;
                }

                $app->createAndSendMailMessage($mailData);
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
            $this->json(true);
        });

        $app->hook('POST(<<agent|space|event|project|opportunity>>.sendSuggestionMessage)', function() use ($plugin) {
            $app = App::i();

            // Se não recebemos o token, não há motivo para avançar para a verificação
            if (!isset($_POST["g-recaptcha-response"]) || empty($_POST["g-recaptcha-response"])) {
                throw new \Exception(\MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.'));
            }

            //Verificando recaptcha v2
            if (!$app->verifyCaptcha($_POST['g-recaptcha-response'])) {
                throw new \Exception(\MapasCulturais\i::__('Recaptcha não selecionado ou inválido, tente novamente.'));
            }

            $entity = $app->repo($this->entityClassName)->find($this->data['entityId']);
            $message = "";
            if(array_key_exists('anonimous',$this->data) && $this->data['anonimous']) {
                $person = \MapasCulturais\i::__("Anônimo");
                $person_email = \MapasCulturais\i::__("Anônimo");
            } else {
                $person = $this->data['name'];
                $person_email = $this->data['email'];
            }

            $dataValue = [
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
                
                $only_to_owner = $this->data['only_owner'] ?? false;

                $suggestion_mail = ['from' => $app->config['mailer.from'], 'subject' => $message['title'], 'body' => $message['body']];
                $destinatarios = $plugin->getSuggestionRecipients($app, $entity);

                $tos = $destinatarios['to'];
                $bccs = $destinatarios['bcc'];
                
                $app->applyHook('mapasculturais.suggestionMessage.destination_to', [&$tos]);
                $app->applyHook('mapasculturais.suggestionMessage.destination_bcc', [&$bccs]);

                if ($only_to_owner) {
                    $suggestion_mail['to'] = $tos;
                } else {
                    $suggestion_mail['to'] = $tos;
                    if (!empty($bccs)) {
                        $suggestion_mail['bcc'] = $bccs;
                    }
                }

                $app->createAndSendMailMessage($suggestion_mail);

                if(isset($entity->owner->user->email) && !empty($entity->owner->user->email)) {
                    if(array_key_exists('anonimous', $this->data) && $this->data['anonimous']) {
                        $email = "<Anonimous>";
                    } else {
                        $email = $entity->owner->user->email;
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
            $this->json(true);  
        });
    }

    public function register() { }

    public function addConfigToJs()
    {
        /** @var App $app */
        $app = App::i();

        $config = [
            'recaptcha' => [
                'sitekey' =>  $app->_config['app.recaptcha.key'] ?? '',
            ]
        ];

        $app->view->jsObject['complaintSuggestionConfig'] = $config;
    }
}
