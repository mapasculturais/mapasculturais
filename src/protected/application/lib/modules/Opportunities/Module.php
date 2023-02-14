<?php

namespace Opportunities;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\i;


class Module extends \MapasCulturais\Module{

    function __construct(array $config = [])
    {
        $app = App::i();
        parent::__construct($config);
    }

    function _init(){

        /** @var App $app */
        $app = App::i();

        // Registro de Jobs
        $app->registerJobType(new Jobs\StartEvaluationPhase(Jobs\StartEvaluationPhase::SLUG));
        $app->registerJobType(new Jobs\StartRegistrationPhase(Jobs\StartRegistrationPhase::SLUG));
        $app->registerJobType(new Jobs\StartPhaseDataCollection(Jobs\StartPhaseDataCollection::SLUG));

        // Executa Job no início da fase
        $app->hook("entity(Opportunity).save:finish ", function() use ($app){
            $data = ['opportunity' => $this];

            if ($this->registrationFrom) {
                $app->enqueueJob(Jobs\StartRegistrationPhase::SLUG, $data, $this->registrationFrom->format("Y-m-d H:i:s"));
            }
        });

        // Executa Job no início da avaliação
        $app->hook("entity(EvaluationMethodConfiguration).save:finish ", function() use ($app){
            $data = ['opportunity' => $this->opportunity];

            if ($this->evaluationFrom) {
                $app->enqueueJob(Jobs\StartEvaluationPhase::SLUG, $data, $this->evaluationFrom->format("Y-m-d H:i:s"));
            }
            
        });

        /* == ROTAS DO PAINEL == */
        // Executa Job no início de uma fase de coleta de dados
        $app->hook("module(OpportunityPhases).createNextPhase(<<*>>):after", function() use ($app){
            if($this->isDataCollection){
                $data = ['opportunity' => $this];
                $app->enqueueJob(Jobs\StartPhaseDataCollection::SLUG, $data, $this->registrationFrom->format("Y-m-d H:i:s"));
            }
        });

        // Executa Job no momento da publicação automática dos resultados da fase
        $app->hook("entity(Opportunity).save:finish", function() use ($app){
            if($this->publish_timestamp){
                $data = ['opportunity' => $this];            
                $app->enqueueJob(Jobs\PublisResultPhase::SLUG, $data, $this->publish_timestamp->format("Y-m-d H:i:s"));
            }
        });
        
          //Cria painel de prestação de contas
        $app->hook('GET(panel.opportunities)', function() use($app) {
            $this->requireAuthentication();
            $this->render('opportunities', []);
        });

        $app->hook('GET(panel.registrations)', function() use($app) {
            $this->requireAuthentication();
            $this->render('registrations', []);
        });

        $app->hook('panel.nav', function(&$nav_items){
            $nav_items['opportunities']['items'] = [
                ['route' => 'panel/opportunities', 'icon' => 'opportunity', 'label' => i::__('Minhas oportunidades')],
                ['route' => 'panel/registrations', 'icon' => 'opportunity', 'label' => i::__('Minhas inscrições')],
                ['route' => 'panel/accountability', 'icon' => 'opportunity', 'label' => i::__('Prestações de contas')],
            ];
        });

        $app->hook('Theme::useOpportunityAPI', function () use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->enqueueScript('components', 'opportunities-api', 'js/OpportunitiesAPI.js', ['components-api']);
        });

        $app->hook('mapas.printJsObject:before', function() use($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->jsObject['config']['evaluationMethods'] = $app->getRegisteredEvaluationMethods();
        });
    }

    function register(){
        
            $app = App::i();
            $controllers = $app->getRegisteredControllers();
            if (!isset($controllers['opportunities'])) {
                $app->registerController('opportunities', Controller::class);
            }
           
    }
}