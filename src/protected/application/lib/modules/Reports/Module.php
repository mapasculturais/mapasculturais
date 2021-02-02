<?php

namespace Reports;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    
    function _init()
    {
        $app = App::i();

        $self = $this;
        
        // Adiciona a aba do módulo de relatórios
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            $this->part('opportunity-reports--tab');
        });

        //Adiciona o conteúdo dentro da aba dos relatórios
        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app, $self) {
            $opportunity = $this->controller->requestedEntity;
            
            if($opportunity->canUser('@control')){
                $this->part('opportunity-reports', [
                    'registrationByTime' => $self->registrationsByTime($opportunity),
                    'registrationsByStatus' => $self->registrationsByStatus($opportunity),
                    'registrationsByEvaluation' => $self->registrationsByEvaluation($opportunity)
                
                ]);
            }
           
        });
    }

    function register()
    {
        $app = App::i();

        $app->registerController('reports', Controller::class);

        $self = $this;
        $app->hook('view.includeAngularEntityAssets:after', function () use ($self) {
            $self->enqueueScriptsAndStyles();
        });
    }
    
    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueStyle('app', 'reports', 'css/reports.css');
        $app->view->enqueueScript('app', 'reports', 'js/ng.reports.js', ['entity.module.opportunity']);       
        $app->view->jsObject['angularAppDependencies'][] = 'ng.reports';
    }
    
    /**
     * Inscrições VS tempo
     * 
     * @return array
     */
    public function registrationsByTime($opp): array
    {
        $app = App::i();       

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $initiated = [];
        $sent = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT 
        to_char(create_timestamp , 'YYYY-MM-DD') as date, 
        count(*) as total 
        FROM registration r 
        WHERE opportunity_id = :opportunity_id
        GROUP BY to_char(create_timestamp , 'YYYY-MM-DD')
        ORDER BY date";
        $result = $conn->fetchAll($query, $params);
        foreach ($result as $value){
            $initiated[$value['date']] = $value['total'];
        }

        $query = "SELECT 
        to_char(sent_timestamp , 'YYYY-MM-DD') as date, 
        count(*) as total 
        FROM registration r 
        WHERE opportunity_id = :opportunity_id
        GROUP BY to_char(sent_timestamp , 'YYYY-MM-DD')
        ORDER BY date";
        $result = $conn->fetchAll($query, $params);
        
        foreach ($result as $value){
            $sent[$value['date']] = $value['total'];
        }
        
        return ["initiated" => $initiated, 'sent' => $sent];
    }

     /**
     * Inscrições agrupadas por status
     * 
     * @return array
     */
    public function registrationsByStatus($opp): array
    {
        $app = App::i();       

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity_id GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value){
            switch ($value['status']) {
                case 0:
                    $status = "Rascunho";
                    break;
                case 1:
                    $status = "Pendente";
                    break;
                case 2:
                    $status = "Inválida";
                    break;
                case 3:
                    $status = "Não Selecionada";
                    break;
                case 8:
                    $status = "Suplente";
                    break;
                case 10:
                    $status = "Selecionada";
                    break;
            }

            $data[$status] = $value['count'];
        }
        
        return $data;
    }

    /**
     * Inscrições agrupadas por status
     * 
     * @return array
     */
    public function registrationsByEvaluation($opp): array
    {
        $app = App::i();       

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT count(*) as evaluated FROM registration r WHERE r.id IN (SELECT re.registration_id FROM registration_evaluation re) AND r.opportunity_id = :opportunity_id";

        $evaluated = $conn->fetchAll($query, $params);

        $query = "SELECT count(*) as notEvaluated FROM registration r WHERE r.id NOT IN (SELECT re.registration_id FROM registration_evaluation re) AND r.opportunity_id = :opportunity_id";

        $notEvaluated = $conn->fetchAll($query, $params);

        
        
        return array_merge($evaluated, $notEvaluated);
    }
    
   
}
