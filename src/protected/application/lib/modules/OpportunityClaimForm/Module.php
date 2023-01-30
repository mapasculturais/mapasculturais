<?php
namespace OpportunityClaimForm;

use MapasCulturais\App,
    MapasCulturais\i;


class Module extends \MapasCulturais\Module{
    function _init(){
        $app = App::i();

        // Define a permissão de modificação da inscrição true apoos enviada caso o upload de arquivo seja do recurso
        $app->hook('can(RegistrationFile.<<*>>)', function($user, &$result){
            /** @var \MapasCulturais\Entities\RegistrationFile $this */
            if($this->group === "formClaimUpload" && $this->owner->opportunity->publishedRegistrations && $this->owner->owner->canUser('@control') ){
                $result = true;
            }
        });

        $app->hook('template(registration.view.registration-sidebar-rigth):end', function () {
            $registration = $this->controller->requestedEntity;
            $opportunity = $registration->opportunity;

                if($registration->canUser('sendClaimMessage')){
                    $this->part('claim-form',['registration' => $registration, 'opportunity' => $opportunity],['opportunity' => $this->controller->requestedEntity]);
                };
        });

        $app->hook('mapasculturais.head', function() use($app){
            $app->view->jsObject['angularAppDependencies'][] = 'ng.opportunity-claim';
            $app->view->enqueueScript('app', 'ng.opportunity-claim', 'js/ng.opportunity-claim.js');
            $app->view->localizeScript('opportunityClaim', [
                'emptyMessage' => i::__('Por favor escreva a mensagem'),
                'claimSended' => i::__('Solicitação de recurso enviada'),
                'claimSendError' => i::__('Ocorreu um erro ao tentar enviar o recurso')
            ]);

        });

        // permissão de enviar recurso
        $app->hook('entity(Registration).canUser(sendClaimMessage)', function($user, &$canUser){
            $opportunity = $this->opportunity;

            // se o status for maior que 0 significa que a inscrição foi enviada
            if($this->status > 0 && $opportunity->publishedRegistrations && !$opportunity->claimDisabled && $this->canUser('view')){
                $canUser = true;
            } else {
                $canUser = false;
            }
        });

        // adiciona seção de configuração do formulário de recurso
        $app->hook("view.partial(singles/opportunity-registrations--export):after", function(){
            $this->part('claim-configuration', ['opportunity' => $this->controller->requestedEntity]);
        });

        // adiciona o botão de recurso na lista de
        $app->hook("template(opportunity.<<*>>.user-registration-table--registration--status):end", function ($registration, $opportunity){
            if($registration->canUser('sendClaimMessage')){
                $this->part('claim-form', ['registration' => $registration, 'opportunity' => $opportunity]);
            }
        });

        // ação de enviar recurso
        $app->hook('POST(opportunity.sendOpportunityClaimMessage)', function() use($app) {
            if(!isset($this->data['registration_id'])) {
                $this->errorJson(i::__("Inscrição não encontrada"), 404);
            }

            $registration = $app->repo('Registration')->find($this->data['registration_id']);

            if(!$registration){
                $this->errorJson(i::__("Inscrição não encontrada"), 404);
            }

            $registration->checkPermission('sendClaimMessage');

            $opportunity = $registration->opportunity;

            $dataValue = [
                'opportunityName' => $opportunity->name,
                'opportunityUrl' => $opportunity->singleUrl,
                'registrationNumber' => $registration->number,
                'registrationUrl' => $registration->singleUrl,
                'date' => date('d/m/Y H:i:s',$_SERVER['REQUEST_TIME']),
                'message' => $this->data['message'],
                'userName' => $app->user->profile->name,
                'userUrl' => $app->user->profile->url,
            ];

            $message = $app->renderMailerTemplate('opportunity_claim', $dataValue);

            $email_to = $opportunity->claimEmail;

            if(!$email_to){
                $email_to = $opportunity->owner->emailPrivado ? $opportunity->owner->emailPrivado : $opportunity->owner->emailPublico;
            }

            if(array_key_exists('mailer.from',$app->config) && !empty(trim($app->config['mailer.from']))) {
                /*
                 * Envia e-mail para o administrador da Oportunidade
                 */
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $email_to,
                    'subject' => $message['title'],
                    'body' => $message['body']
                ]);
            }
        });
    }

    /*
     * Send opportunity claim message (mail and notification)
     */
    public function POST_sendOpportunityClaimMessage() {
        $app = App::i();


    }

    function register(){
        $app = App::i();
        

        $this->registerOpportunityMetadata('claimDisabled', [
            'label' => i::__('Desabilitar formulário de recursos'),
            'type' => 'select',
            'options' => (object)[
                '0' => i::__('formulário de recurso habilitado'),
                '1' => i::__('formulário de recurso desabilitado'),
            ]
        ]);

        $this->registerOpportunityMetadata('claimEmail', [
            'label' => \MapasCulturais\i::__('Email de destino do formulário de recursos'),
            'validations' => [
                'v::email()' => \MapasCulturais\i::__('Email inválido')
            ]
        ]);

        $app->registerFileGroup('registration', new \MapasCulturais\Definitions\FileGroup('formClaimUpload',['application/pdf'], 'O formato do arquivo é inválido', false, null, true));
    }
}
