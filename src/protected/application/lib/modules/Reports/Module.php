<?php

namespace Reports;

use DateTime;
use DatePeriod;
use DateInterval;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Definitions\MetaListGroup;
use MapasCulturais\i;


class Module extends \MapasCulturais\Module
{

    protected $chartColors = [
        'colors' => ['#333333','#1c5690','#b3b921','#1dabc6','#e83f96','#cc0033','#9966cc','#40b4b4','#cc9933','#cc3333','#66cc66','#003c46','#d62828','#5a189a','#00afb9','#38b000','#3a0ca3','#489fb5','#245501','#708d81','#00bbf9','#f15bb5','#ffdab9','#5f0f40','#e9ff70','#fcf6bd','#4a5759','#06d6a0','#cce3de','#f3ac01'],
        'pointer' => 0
    ];

    public function __construct(array $config = [])
    {
        $app = App::i();

        $config += [
            'agent' => $app->config['report.agent'],
            'collective' => $app->config['report.collective'],
        ];
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();

        $self = $this;

        // Adiciona a aba do módulo de relatórios
        $app->hook('template(opportunity.single.tabs):end', function () use ($app, $self) {
            if ($this->controller->requestedEntity->canUser("@control") && $self->hasRegistrations($this->controller->requestedEntity)) {
                $this->part('opportunity-reports--tab');
            }
        });

        //Adiciona o conteúdo dentro da aba dos relatórios

        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app, $self) {
        	$request = $this->controller->data;
        	$statusValue =  $request['status'] ?? 'all';

            switch ($statusValue) {
                case 'all':
                    $status = '> 0';
                    break;
                case 'draft':
                    $status = '= 0';
                    break;
                case 'approved':
                    $status = '= 10';
                    break;
                default:
                    $status = '> 0';
                    break;
            }

            $_SESSION['reportStatusRegistration'] = $status;

	        $app->view->jsObject['reportStatus'] = $statusValue;

            $opportunity = $this->controller->requestedEntity;
            $sendHook = [];

            if(!$opportunity->isOpportunityPhase){
                if ($registrationsByTime = $self->registrationsByTime($opportunity, $status)) {
                    $sendHook['registrationsByTime'] = $registrationsByTime;
                }
            }

            if ($registrationsByStatus = $self->registrationsByStatus($opportunity)) {
                $sendHook['registrationsByStatus'] = $registrationsByStatus;
            }

            if ($opportunity->evaluationMethod->slug == 'technical') {
                if ($registrationsByEvaluation = $self->registrationsByEvaluationStatusBar($opportunity)) {
                    $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
                }
            } else {
                if ($registrationsByEvaluation = $self->registrationsByEvaluation($opportunity, $statusValue)) {
                    $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
                }
            }

            if ($registrationsByCategory = $self->registrationsByCategory($opportunity)) {
                $sendHook['registrationsByCategory'] = $registrationsByCategory;
            }

            $sendHook['opportunity'] = $opportunity;

            $sendHook['self'] = $self;
            $sendHook['statusRegistration'] = $statusValue;

            if ($opportunity->canUser('@control') && $self->hasRegistrations($opportunity)) {
                $this->part('opportunity-reports', $sendHook);
            }

        });

        $app->hook('template(opportunity.single.reports-footer):before', function () use ($app, $self) {
            $this->part('create-reports-modal', []);
        });

        $app->hook('template(opportunity.single.reports-footer):before', function () {
            $this->part('dynamic-reports');
        });

        $app->hook('mapasculturais.head', function () use ($app, $self) {
            $app->view->jsObject['chartColors'] = $self->chartColors;
        });

    }

    public function register()
    {
        $app = App::i();

        $app->registerController('reports', Controller::class);

        $self = $this;
        $app->hook('view.includeAngularEntityAssets:after', function () use ($self) {
            $self->enqueueScriptsAndStyles();
        });

        $metalist = new MetaListGroup('reports',
            [
                'title' => [
                    'label' => 'Titulo'
                ],
                'value' => [
                    'label' => 'Gráfico',
                    'validations' => [
                        'required' => '',
                    ]
                ],
            ],
            \MapasCulturais\i::__(''),
            true
        );
        $app->registerMetaListGroup('reports', $metalist);
    }

    public function enqueueScriptsAndStyles()
    {
        $app = App::i();

        $app->view->enqueueStyle('app', 'reports', 'css/reports.css');
        $app->view->enqueueScript('app', 'reports', 'js/ng.reports.js', ['entity.module.opportunity']);
        $app->view->jsObject['angularAppDependencies'][] = 'ng.reports';
    }

    /**
     * Verifica se a oportunidade passada como parâmetro possui inscrições
     */
    public function hasRegistrations(\MapasCulturais\Entities\Opportunity $opportunity)
    {
        $app = App::i();
        $conn = $app->em->getConnection();

        $registrations = $conn->fetchAll("SELECT id FROM registration WHERE opportunity_id = $opportunity->id");

        if (count($registrations) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se existem dados suficientes para gerar o gráfico
     */
    public function checkIfChartHasData(array $values) {

        if (count($values) > 1) {

            $count = 0;
            foreach ($values as $key => $value) {
                if ($value > 1)
                    $count++;
            }

            if ($count >= 2){
                return true;
            }

            return false;

        }

        return false;

    }

    /**
     * Inscrições VS tempo
     *
     *
     */
    public function registrationsByTime($opp)
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
        ORDER BY date ASC";
        $result = $conn->fetchAll($query, $params);
        foreach ($result as $value) {
            $initiated[$value['date']] = $value['total'];
            $date['create_timestamp'][] = $value['date'];
        }

        $query = "SELECT
        to_char(sent_timestamp , 'YYYY-MM-DD') as date,
        count(*) as total
        FROM registration r
        WHERE opportunity_id = :opportunity_id AND r.status > 0
        GROUP BY to_char(sent_timestamp , 'YYYY-MM-DD')
        ORDER BY date ASC";
        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value) {
            $sent[$value['date']] = $value['total'];
            $date['sent'][] = $value['date'];
        }

        if (!$sent || !$initiated) {
            return false;
        }

        $merge = array_merge($date['sent'], $date['create_timestamp']);

        $end = (new DateTime(max(array_column($merge,null))))->modify('+1 day');

        $period = new DatePeriod(
            new DateTime(min(array_column($merge,null))),
            new DateInterval('P1D'),
            $end
       );

        $range = [];
        foreach ($period as $key => $value){
          $range[] = $value->format('Y-m-d');
        }

        $ini = [];
        $sen = [];
        foreach ($range as $date){
            if(!isset($initiated[$date])){
                $ini[$date] = 0;
            }else{
                $ini[$date] = $initiated[$date];
            }

            if(!isset($sent[$date])){
                $sen[$date] = 0;
            }else{
                $sen[$date] = $sent[$date];
            }
        }


        return ['Finalizadas' => $sen, "Iniciadas" => $ini];

    }

    /**
     * Inscrições agrupadas por status
     *
     *
     */
    public function registrationsByStatus($opp)
    {
        $app = App::i();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity_id GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        $status_names = [
            '0' => i::__('Rascunho'),
            '1' => i::__('Pendente'),
            '2' => i::__('Inválida'),
            '3' => i::__('Não Selecionada'),
            '8' => i::__('Suplente'),
            '10' => i::__('Selecionada')
        ];

        $data = [
                i::__('Rascunho') => 0,
                i::__('Pendente') => 0,
                i::__('Inválida') => 0,
                i::__('Não Selecionada') => 0,
                i::__('Suplente') => 0,
                i::__('Selecionada') => 0
            ];

        foreach ($result as $value) {

            $status = $status_names[$value['status']] ?? null;

            if (!$status) {
                continue;
            }

            $data[$status] = $value['count'];
        }

        return $data;
    }

    /**
     * Inscrições agrupadas por avaliação
     *
     *
     */
    public function registrationsByEvaluation($opp, $statusValue)
    {
        switch ($statusValue) {
            case 'all':
                $status = '> 0';
                break;
            case 'draft':
                $status = '= 0';
                break;
            case 'approved':
                $status = '= 10';
                break;
            default:
                $status = '> 0';
                break;
        }
        
        $complement = "";
        if($status != "> 0"){
            $complement = " AND status $status";
        }

        $app = App::i();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT count(*) AS evaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result <> '0' {$complement}";

        $evaluated = $conn->fetchAll($query, $params);

        $query = "SELECT COUNT(*) AS notEvaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result = '0' {$complement}";

        $notEvaluated = $conn->fetchAll($query, $params);

        $merge = array_merge($evaluated, $notEvaluated);
        
        if($statusValue == "all"){
            foreach ($merge as $m) {
                foreach ($m as $v) {
                    if (empty($v)) {
                        return false;
                    }
                }
            }
        }

        return $merge;
    }

    /**
     * Inscrições agrupadas por status da avaliação
     *
     *
     */
    public function registrationsByEvaluationStatus(Opportunity $opp)
    {
        $app = App::i();

        $em = $opp->getEvaluationMethod();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT COUNT(*), consolidated_result FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result <> '0' GROUP BY consolidated_result";

        $evaluations = $conn->fetchAll($query, $params);

        $count = 0;
        foreach ($evaluations as $evaluation) {
            if ($count < 8) {
                $data[$em->valueToString($evaluation['consolidated_result'])] = $evaluation['count'];
                $count++;
            }
        }

        return $data;

    }

    /**
     * Inscrições agrupadas por status da avaliação
     *
     *
     */
    public function registrationsByEvaluationStatusBar(Opportunity $opp)
    {
        $app = App::i();

        $em = $opp->getEvaluationMethod();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo

        $params = ['opportunity_id' => $opp->id];

        $result = [];
        $a = 0;
        $b = 20;
        $label = "";
        for ($i = 0; $i < 100; $i += 20) {

            if ($i > 0) {
                $a = $b + 1;
                $b = $b + 20;
            }

            $query = "SELECT count(consolidated_result)
            FROM registration r
            WHERE opportunity_id = :opportunity_id
            AND consolidated_result <> '0' AND
            cast(consolidated_result as DECIMAL) BETWEEN {$i} AND {$b}";

            $label = "de " . $a . " a " . $b;

            $result[$label] = $conn->fetchAll($query, $params);

        }

        $data = [];
        foreach ($result as $key => $value) {
            $data[$key] = $value[0]['count'];

        }

        return $data;
    }

    /**
     * Inscrições agrupadas pela vategoria
     *
     *
     */
    public function registrationsByCategory(Opportunity $opp)
    {
        $app = App::i();

        $em = $opp->getEvaluationMethod();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "select  category, count(category) from registration r where r.status > 0 and r.opportunity_id = :opportunity_id group by category";

        $data = $conn->fetchAll($query, $params);

        foreach ($data as $value) {
            foreach ($value as $v) {
                if (empty($v)) {
                    return false;
                }
            }
        }

        return $data;

    }

    /**
     * Retorna cores para os gráficos
     */
    public function getChartColors($quantity = 1)
    {

        $pointer = $this->chartColors['pointer'];
        $colors = [];

        for ($i = 0; $i < $quantity; $i++) {
            $colors[] = $this->chartColors['colors'][$pointer];

            $pointer++;
            if ($pointer >= count($this->chartColors['colors'])) {
                $pointer = 0;
            }
        }
        $this->chartColors['pointer'] = $pointer;

        return $colors;

    }

}
