<?php

namespace Reports;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Definitions\MetaListGroup;


class Module extends \MapasCulturais\Module
{
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
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            if ($this->controller->requestedEntity->canUser("@control")) {
                $this->part('opportunity-reports--tab');
            }
        });

        //Adiciona o conteúdo dentro da aba dos relatórios

        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app, $self) {
            $opportunity = $this->controller->requestedEntity;
            $dataOportunity = $opportunity->getEvaluationCommittee();
            $sendHook = [];

            if ($registrationsByTime = $self->registrationsByTime($opportunity)) {
                $sendHook['registrationsByTime'] = $registrationsByTime;
            }

            if ($registrationsByStatus = $self->registrationsByStatus($opportunity)) {
                $sendHook['registrationsByStatus'] = $registrationsByStatus;
            }

            if ($registrationsByEvaluationStatus = $self->registrationsByEvaluationStatus($opportunity)) {
                $sendHook['registrationsByEvaluationStatus'] = $registrationsByEvaluationStatus;
            }

            if ($dataOportunity[0]->owner->type == 'technical') {
                if ($registrationsByEvaluation = $self->registrationsByEvaluationStatusBar($opportunity)) {
                    $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
                }
            } else {
                if ($registrationsByEvaluation = $self->registrationsByEvaluation($opportunity)) {
                    $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
                }
            }

            if ($registrationsByCategory = $self->registrationsByCategory($opportunity)) {
                $sendHook['registrationsByCategory'] = $registrationsByCategory;
            }

            $sendHook['opportunity'] = $opportunity;

            $sendHook['color'] = function () use ($self) {
                return $self->color();
            };

            if ($opportunity->canUser('@control')) {
                $this->part('opportunity-reports', $sendHook);
            }

        });

        $app->hook('template(opportunity.single.reports-footer):before', function () use ($app, $self) {
            $this->part('create-reports-modal', []);
        });

        $app->hook('template(opportunity.single.reports-footer):before', function () {
            $this->part('dynamic-reports');
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
        }

        if (!$sent || !$initiated) {
            return false;
        }

        return ['Finalizadas' => $sent, "Iniciadas" => $initiated];

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

        foreach ($result as $value) {
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

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * Inscrições agrupadas por avaliação
     *
     *
     */
    public function registrationsByEvaluation($opp)
    {
        $app = App::i();

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "SELECT count(*) AS evaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result <> '0'";

        $evaluated = $conn->fetchAll($query, $params);

        $query = "SELECT COUNT(*) AS notEvaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result = '0'";

        $notEvaluated = $conn->fetchAll($query, $params);

        $merge = array_merge($evaluated, $notEvaluated);

        foreach ($merge as $m) {
            foreach ($m as $v) {
                if (empty($v)) {
                    return false;
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

    public function color()
    {
        mt_srand((double) microtime() * 1000000);
        $c = '';
        while (strlen($c) < 6) {
            $c .= sprintf("%02X", mt_rand(0, 255));
        }
        return "#" . $c;
    }

}
