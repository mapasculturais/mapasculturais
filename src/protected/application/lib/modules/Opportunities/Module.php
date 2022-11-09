<?php

namespace Opportunities;

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
        $app->hook('GET(panel.opportunities)', function() use($app) {
            $this->requireAuthentication();
            $this->render('opportunities', []);
        });

        $app->hook('GET(panel.registrations)', function() use($app) {
            $this->requireAuthentication();
            $this->render('registrations', []);
        });

        $app->hook('panel.nav', function(&$nav_items){
            $nav_items['opportunities'] = [ 
                'label' => i::__('Editais e oportunidades'),
                'items' => [
                    ['route' => 'panel/opportunities', 'icon' => 'opportunity', 'label' => i::__('Minhas oportunidades')],
                    ['route' => 'panel/registrations', 'icon' => 'opportunity', 'label' => i::__('Minhas inscrições')],
                    ['route' => 'panel/accountability', 'icon' => 'opportunity', 'label' => i::__('Prestações de contas')],
                ]
            ];
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