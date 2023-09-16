<?php
namespace OpportunityClaimForm;

use MapasCulturais\App,
    MapasCulturais\i;


class Module extends \MapasCulturais\Module{
    function _init(){
        $app = App::i();

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

            $claim_phase = $registration->opportunity;
            $opportunity = $registration->opportunity->firstPhase;

            $phase_name = '';
            $num = 0;
            foreach($opportunity->phases as $phase) {
                $num++;
                if ($phase->{'@entityType'} == 'opportunity' && $phase->id == $claim_phase->id) {
                    $phase_name = $num == 1 ? i::__('Período de inscrição') : $claim_phase->name;
                    if ($claim_phase->evaluationMethodConfiguration) {
                        $n2 = $num + 1;
                        $phase_name = "{$num}. {$phase_name} / {$n2}. {$claim_phase->evaluationMethodConfiguration->name}";
                    } else {
                        $phase_name = "{$num}. {$phase_name}";
                    }
                    break;
                }
            }

            $dataValue = [
                'opportunityName' => $opportunity->name,
                'phaseName' => $phase_name,
                'opportunityUrl' => $opportunity->singleUrl,
                'registrationNumber' => $registration->number,
                'registrationUrl' => $registration->singleUrl,
                'date' => date('d/m/Y H:i:s',$_SERVER['REQUEST_TIME']),
                'message' => $this->data['message'],
                'userName' => $app->user->profile->name,
                'userUrl' => $app->user->profile->singleUrl,
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
                    'subject' => sprintf(i::__('Solicitação de Recurso na Oportunidade %s'), $opportunity->name),
                    'body' => $message['body']
                ]);
            }
        });

        
        $app->hook('app.init:after', function () use($app) {
            if ($app->view->version >= 2) {
                $app->hook("module(OpportunityPhases).dataCollectionPhaseData", function(&$data) {
                    $data .= ',claimEmail,claimDisabled';
                });
                
                $app->hook("component(opportunity-phase-config-<<data-collection|evaluation|results>>):bottom", function(){
                    $this->part('opportunity-claim-config');
                });

                $app->hook('component(opportunity-phases-timeline).registration:end', function () {
                    $registration = $this->controller->requestedEntity;
                    if($registration->canUser('sendClaimMessage')){
                        $this->part('opportunity-claim-form-component');
                    }
                });

            } else {
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

        $this->registerOpportunityMetadata('claimDisabled', [
            'label' => i::__('Desabilitar formulário de recursos'),
            'type' => 'boolean'
        ]);

        $this->registerOpportunityMetadata('claimEmail', [
            'label' => \MapasCulturais\i::__('Email de destino do formulário de recursos'),
            'validations' => [
                'v::email()' => \MapasCulturais\i::__('Email inválido')
            ]
        ]);
    }
}