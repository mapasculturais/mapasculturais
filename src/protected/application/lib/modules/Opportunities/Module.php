<?php

namespace Opportunities;

use MapasCulturais\App;

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

            $app->enqueueJob(Jobs\StartRegistrationPhase::SLUG, $data, $this->registrationFrom->format("Y-m-d H:i:s"));
        });

        // Executa Job no início da avaliação
        $app->hook("entity(EvaluationMethodConfiguration).save:finish ", function() use ($app){
            $data = ['opportunity' => $this->opportunity];

            if($this->evaluationFrom){
                $app->enqueueJob(Jobs\StartEvaluationPhase::SLUG, $data, $this->evaluationFrom->format("Y-m-d H:i:s"));
            }
            
        });

        // Executa Job no início de uma fase de coleta de dados
        $app->hook("module(OpportunityPhases).createNextPhase(<<*>>):after", function() use ($app){
            if($this->opportunity_data_collection){
                $data = ['opportunity' => $this];
                $app->enqueueJob(Jobs\StartPhaseDataCollection::SLUG, $data, $this->registrationFrom->format("Y-m-d H:i:s"));
            }
        });
    }

    function register(){
        
            $app = App::i();
            $controllers = $app->getRegisteredControllers();
            if (!isset($controllers['opportunities'])) {
                $app->registerController('opportunities', Controller::class);
            }

            $this->registerOpportunityMetadata("opportunity_data_collection", [
                'label'=> "Define se é uma oportunidade de coleta de dados",
                'type'=>'bool',
                'private'=> true,
                'default'=> false,
            ]);
    }
}