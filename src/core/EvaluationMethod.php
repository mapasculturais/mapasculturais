<?php
namespace MapasCulturais;

use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use MapasCulturais\Entities;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\User;

abstract class EvaluationMethod extends Module implements \JsonSerializable{
    abstract protected function _register();

    abstract function enqueueScriptsAndStyles();

    abstract function getSlug();
    abstract function getName();
    abstract function getDescription();
    
    abstract protected function _valueToString($value);

    abstract protected function _getConsolidatedResult(Entities\Registration $registration, array $evaluations);
    abstract function getEvaluationResult(Entities\RegistrationEvaluation $evaluation);

    abstract function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): ?array;
    abstract function _getConsolidatedDetails(Entities\Registration $registration): ?array;


    static function getNextRedistributionDateTime(): \DateTime {
        $app = App::i();
        $str_time = date($app->config['registrations.distribution.dateString']) . ' ' . $app->config['registrations.distribution.incrementString'];
        $datetime = new \DateTime($str_time);
        return $datetime;
    }

    public function cmpValues($value1, $value2){
        if($value1 > $value2){
            return 1;
        } elseif($value1 < $value2){
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * Filtra o resultado do sumário da fase de avaliação
     * 
     * @param array $data 
     * @return array 
     */
    public function filterEvaluationsSummary(array $data) {
        return $data;
    }

    /**
     * @param Entities\RegistrationEvaluation $evaluation
     *
     * @return array of errors
     */
    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data){
        return [];
    }

    function getReportConfiguration($opportunity, $call_hooks = true){
        $app = App::i();

        // Registration Section Columns
        $registration_columns = [];
        if($opportunity->projectName){
            $registration_columns['projectName'] = (object) [
                'label' => i::__('Nome do projeto'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->projectName;
                }
            ];
        }

        if($opportunity->registrationCategories){
            $registration_columns['category'] = (object) [
                'label' => i::__('Categoria de inscrição'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->category;
                }
            ];
        }

        $registration_columns = $registration_columns + [
            'owner' => (object) [
                'label' => i::__('Agente Responsável'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->owner->name;
                }
            ],
            'number' => (object) [
                'label' => i::__('Número de inscrição'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->number;
                }
            ],
        ];


        /*
         * @TODO: adicionar as colunas abaixo:
         * - tempo de permanência na avaliacao
         */
        $committee_columns = [
            'evaluator' => (object) [
                'label' => i::__('Nome'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->user->profile->name;
                }
            ]
        ];


        $evaluation_columns = [
            'result' => (object) [
                'label' => i::__('Resultado'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->getResultString();
                }
            ],
            'status' => (object) [
                'label' => i::__('Status'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->getStatusString();
                }
            ],
        ];

        $sections = [
            'registration' => (object) [
                'label' => i::__('Informações sobre as inscrições e proponentes'),
                'color' => '#CCCCFF',
                'columns' => $registration_columns
            ],

            'committee' => (object) [
                'label' => i::__('Informações sobre o avaliador'),
                'color' => '#CCFFCC',
                'columns' => $committee_columns
            ],

            'evaluation' => (object) [
                'label' => i::__('Avaliação'),
                'color' => '#00AA00',
                'columns' => $evaluation_columns
            ]
        ];

        if($call_hooks){
            $app->applyHookBoundTo($this, "evaluationsReport({$this->slug}).sections", [$opportunity, &$sections]);

            foreach($sections as $section_slug => &$section){
                $app->applyHookBoundTo($this, "evaluationsReport({$this->slug}).section({$section_slug})", [$opportunity, &$section]);
            }
        }

        return $sections;
    }


    function evaluationToString(Entities\RegistrationEvaluation $evaluation){
        return $this->valueToString($evaluation->result);
    }

    function valueToString($value) {
        if($value == '@tiebreaker'){
            return i::__('Aguardando desempate');
        }
        return $this->_valueToString($value);
    }

    /** 
     * Retorna a avaliação de desempate de uma inscrição
     * 
     * @param Entities\Registration $registration
     * @return Entities\RegistrationEvaluation|null
     */
    function getTiebreakerEvaluation(Entities\Registration $registration) {
        $app = App::i();

        $tiebreaker_evaluations = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration, 'isTiebreaker' => true]);

        if(empty($tiebreaker_evaluations)){
            return null;
        }

        return $tiebreaker_evaluations;
    }


    /**
     * Retorna o resultado consolidado de uma inscrição
     * 
     * @param Entities\Registration $registration
     * @return mixed
     */
    function getConsolidatedResult(Entities\Registration $registration){
        $app = App::i();
        
        $registration->checkPermission('viewConsolidatedResult');
        $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration, 'status' => RegistrationEvaluation::STATUS_SENT]);

        if(empty($evaluations)){
            return 0;
        }

        $committees = $registration->getCommittees();

        $result = '';

        if($registration->needsTiebreaker()){
            
            $tiebreaker_evaluation = $this->getTiebreakerEvaluation($registration);

            if (empty($tiebreaker_evaluation)) {
                $result =  '@tiebreaker';
            } else {
                $result =  $this->_getConsolidatedResult($registration, [$tiebreaker_evaluation]);
            }
        } else {
            $number_of_valuers = 0;
            foreach($committees as $users){
                $number_of_valuers += count($users);
            }

            if(count($evaluations) < $number_of_valuers){
                $result =  0;
            }
            
            $result =  $this->_getConsolidatedResult($registration, $evaluations);
        }

        return $result;
    }

    /**
     * Retorna os detalhes de uma avaliação
     * 
     * @param Entities\RegistrationEvaluation $evaluation
     * @return array
     */
    function getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): array {
        $app = App::i();
        $result = $this->_getEvaluationDetails($evaluation);
        $app->applyHookBoundTo($evaluation, "{$evaluation->hookPrefix}.details", [&$result]);
        return $result;
    }

    /**
     * Retorna os detalhes consolidados de uma inscrição
     * 
     * @param Entities\Registration $registration
     * @return array
     */
    function getConsolidatedDetails(Entities\Registration $registration): array {
        $app = App::i();
        $result = $this->_getConsolidatedDetails($registration);
        $result['sentEvaluationCount'] = count($registration->sentEvaluations);

        $app->applyHookBoundTo($registration, "{$registration->hookPrefix}.details", [&$result]);
        return $result;
    }

    /**
     * Retorna os usuários avaliadores agrupados pelos comitês de avaliação
     * 
     * @param Opportunity $opportunity 
     * @return Entities\User[][]
     */
    function getCommitteeGroups(Entities\EvaluationMethodConfiguration $evaluation_config): array {
        $cache_key = __METHOD__ . ':' . $evaluation_config->id;
        $app = App::i();

        if($app->rcache->contains($cache_key)){
            return $app->rcache->fetch($cache_key);
        }

        $committee = []; 
        foreach($evaluation_config->getAgentRelations(null, false) as $relation) {
            if($relation->status == EvaluationMethodConfigurationAgentRelation::STATUS_ENABLED) {
                $committee[$relation->group] = $committee[$relation->group] ?? [];
                $committee[$relation->group][] = $relation->agent->user;
            }
        }

        $app->rcache->save($cache_key, $committee);

        return $committee;
    }

    /**
     * Verifica se a fase de avaliação usa um comitê de desempate
     * 
     * @param Entities\EvaluationMethodConfiguration|null $evaluation_method_configuration
     * @return bool
     */ 
    function evaluationPhaseUsesTiebreaker(Entities\EvaluationMethodConfiguration|null $evaluation_method_configuration) {
        $app = App::i();

        if(is_null($evaluation_method_configuration)) {
            return false;
        }

        $cache_key = __METHOD__ . ':' . $evaluation_method_configuration->id;
        if($app->rcache->contains($cache_key)){
            return $app->rcache->fetch($cache_key);
        }

        /** @var Connection */
        $conn = $app->em->getConnection();

        $object_type = Entities\EvaluationMethodConfiguration::class;

        $uses = $conn->fetchScalar("
            SELECT count(*) 
            FROM agent_relation 
            WHERE 
                type = '@tiebreaker' AND 
                object_type = :object_type AND
                object_id = :id", 
                [
                    'object_type' => $object_type,
                    'id' => $evaluation_method_configuration->id
                ]);
        
        $app->rcache->save($cache_key, (bool) $uses);

        return (bool) $uses;
    }

    /** 
     * Verifica se a inscrição precisa de um desempate
     * 
     * @param Entities\Registration $registration
     * @return bool
     */
    function registrationNeedsTiebreaker(Registration $registration): bool {
        $app = App::i();

        if(!$this->evaluationPhaseUsesTiebreaker($registration->evaluationMethodConfiguration)) {
            return false;
        }

        $cache_key = __METHOD__ . ':' . $registration->id;
        if($app->rcache->contains($cache_key)){
            return $app->rcache->fetch($cache_key);
        }

        /** @conn \MapasCulturais\Connection */
        $conn = $app->em->getConnection();

        $evaluations = $conn->fetchAllAssociative("SELECT * FROM evaluations WHERE registration_id = :registration_id AND valuer_committee <> '@tiebreaker'", [
            'registration_id' => $registration->id
        ]);

        // verifica se todas as avaliações que não são da comissão de desempate estão enviadas, se não, não precisa de desempate
        $not_sent_evaluations = array_filter($evaluations, function($evaluation) {
            return $evaluation['evaluation_status'] != RegistrationEvaluation::STATUS_SENT;
        });

        if($not_sent_evaluations) {
            $app->rcache->save($cache_key, false);

            // if($registration->id == 1134470122) {
            //     eval(\psy\sh());
            // }
            return false;
        }

        // obtem as avaliações e agrupa elas por comissão
        $evaluation_ids = array_map(fn($evaluation) => $evaluation['evaluation_id'], $evaluations);
        $evaluations = $app->repo('RegistrationEvaluation')->findBy(['id' => $evaluation_ids]);
        $committee_evaluations = [];
        foreach($evaluations as $evaluation) {
            $committee_evaluations[$evaluation->committee][] = $evaluation;
        }

        // obtem os resultados consolidados de cada comissão
        $results = [];

        foreach($committee_evaluations as $committe => $evaluations) {
            $user_ids = array_map(fn($evaluation) => $evaluation->user->id, $evaluations);

            $group_evaluations = array_filter($evaluations, fn($e) => in_array($e->user->id, $user_ids));

            $results[$committe] = $this->_getConsolidatedResult($registration, $group_evaluations);
        }
        
        // se há mais que um valor no array, então há divergência
        if(count(array_unique($results)) > 1) {
            $result = true;
        } else {
            $result = false;
        }
        

        $app->rcache->save($cache_key, $result);
        return $result;
    }

    public function redistributeRegistrations(Entities\Opportunity $opportunity) {
        ini_set('max_execution_time', 0);
        $start_time = microtime(true);

        $app = App::i();
        $evaluation_config = $opportunity->evaluationMethodConfiguration;

        $log_path = PUBLIC_PATH . "files/distributionslog/";
        if(!is_dir($log_path)){
            mkdir($log_path, 0755, true);
        }
        $log_filename = "$log_path/{$evaluation_config->id}.log";

        /** @var Connection */
        $conn = $app->em->getConnection();

        /** @var Repositories\Registration */
        $repo = $app->repo('Registration');

        $committees = $this->getCommitteeGroups($opportunity->evaluationMethodConfiguration);

        $ignore_started_evaluations = $evaluation_config->ignoreStartedEvaluations;

        /** Limite de avaliadores por inscrição
         * @var array */
        $valuers_per_registration = $evaluation_config->valuersPerRegistration;

        // coloca o comitê de desempate no final do array
        if(isset($committees['@tiebreaker'])) {
            $tiebreaker_committee = $committees['@tiebreaker'];
            unset($committees['@tiebreaker']);
            $committees['@tiebreaker'] = $tiebreaker_committee;
        }

        /** Número de avaliadores da fase
         * @var int */
        $number_of_valuers = 0;
        foreach($committees as $users){
            $number_of_valuers += count($users);
        }

        /** 
         * Número de inscrições que cada avaliador tem por comissão
         * @var array */
        $valuers_registrations_count = [];
        foreach($committees as $committee_name => $valuers) {
            $valuers_registrations_count[$committee_name] = [];
            foreach($valuers as $user) {
                $valuers_registrations_count[$committee_name][$user->id] = 0;
            }
        }


        /**
         * Número de avaliações que cada inscrição tem por comissão
         * 
         * @var array
         */
        $registration_valuers_count = [];

        /** 
         * Resultado final da distribuição das comissões
         * 
         * ```JSON
         * [ 
         *  registrationId: {
         *     // id do usuario avaliador: nome da comissão
         *     33: "Nome da Comissão 1",
         *     66: "Nome da Comissão 1",
         *     88: "Nome da Comissão 2",
         *     109: "@tiebreaker"
         * }
         * ]
         * ```
         * @var array */
        $result = [];

        // registra os metadados dos campos das inscrições
        $opportunity->registerRegistrationMetadata();

        // obtém a lista de inscrições e das avaliações já feitas
        $sql = "
                SELECT 
                    r.id, 
                    r.number, 
                    r.valuers,
                    r.valuers_exceptions_list,
                    v.user_id,
                    v.is_tiebreaker,
                    v.committee,
                    count(e.id) AS num 
                FROM 
                    registration r 
                LEFT JOIN 
                    registration_evaluation e ON e.registration_id = r.id
                LEFT JOIN 
                    registration_evaluation v ON v.registration_id = r.id
                WHERE 
                    opportunity_id = {$opportunity->id} AND
                    r.status = 1

                GROUP BY r.id, v.id
                ORDER BY num ASC
            ";

        /**
         * Lista de inscrições que devem ser distribuidas 
         * @var array */
        $registration_evaluations = $conn->fetchAllAssociative($sql);

        /** Número de verificações
         * @var int */
        $total_checks = count($registration_evaluations) * $number_of_valuers;
        $checks_count = 0;

        // processa a lista de inscrições fazendo as definições iniciais das variáveis
        foreach($registration_evaluations as $registration) {
            $registration_entity = null;
            $registration = (object) $registration;
            $registration->valuers = json_decode($registration->valuers);
            $registration->valuers_exceptions_list = json_decode($registration->valuers_exceptions_list);

            // cria as entradas do $result
            $result[$registration->id] = $result[$registration->id] ?? [];

            // inicializa a contagem de avaliadores das inscrições
            $registration_valuers_count[$registration->id] = $registration_valuers_count[$registration->id] ?? [];
            foreach(array_keys($committees) as $committee_name) {
                $registration_valuers_count[$registration->id][$committee_name] = $registration_valuers_count[$registration->id][$committee_name] ?? 0;
            }
            
            // caso a inscrição já tenha sido avaliada
            if($registration->user_id){
                $committee_name = $registration->committee;
                $user_id = $registration->user_id;

                $result[$registration->id][$user_id] = $committee_name;

                // se a configuração `Desconsiderar as avaliações já feitas na distribuição` estiver desativada
                if(!($ignore_started_evaluations->$committee_name ?? false)) {
                    // atualiza o número de avaliadores da inscrição
                    $valuers_registrations_count[$committee_name][$user_id] = $valuers_registrations_count[$committee_name][$user_id] ?? 0;
                    $valuers_registrations_count[$committee_name][$user_id]++;
                }

                $registration_valuers_count[$registration->id][$committee_name] = $registration_valuers_count[$registration->id][$committee_name] ?? 0;

                // incrementa o número de avaliações que a inscrição tem por comissão
                $registration_valuers_count[$registration->id][$committee_name]++;
            }

            // adiciona os usuários da lista de inclusões (valuers_exceptions_list->include)
            foreach($registration->valuers_exceptions_list->include as $user_id) {
                // se o usuário já é avaliador da inscrição, não precisa adicionar
                if(isset($result[$registration->id][$user_id])) {
                    continue;
                }

                /** 
                 * Lista de comissões que o usuário está
                 * @var array 
                 **/
                $user_committees = [];
                
                // encontra em quais comissões o usuário está
                foreach($committees as $committee_name => $users) {
                    if(in_array($user_id, array_map(fn($user) => $user->id, $users))) {
                        $user_committees[] = $committee_name;
                    }
                }

                if(count($user_committees) > 0) {
                    // escolhe uma das comissões randomicamente para adicionar o usuário
                    $committee_name = $user_committees[array_rand($user_committees)];

                    // adiciona o usuário na comissão
                    $result[$registration->id][$user_id] = $committee_name;

                    // atualiza o número de avaliadores da inscrição
                    $valuers_registrations_count[$committee_name][$user_id]++;

                    // incrementa o número de avaliações que a inscrição tem por comissão
                    $registration_valuers_count[$registration->id][$committee_name]++;
                }
            }

            // passa por cada comissão adicionando os avaliadores até o limite de avaliadores por inscrição configurado na comissão
            foreach($committees as $committee_name => $users) {
                $percent = round(($checks_count / $total_checks) * 100, 2);
                if($app->config['app.log.evaluations']) {
                    // imprime a porcentagem de verificações
                    $app->log->debug("[$percent%] $registration->number - $checks_count de $total_checks");
                }

                file_put_contents($log_filename, "$percent%");

                if($committee_name == '@tiebreaker') {
                    $registration_entity = $registration_entity ?: $repo->find($registration->id);

                    if($this->registrationNeedsTiebreaker($registration_entity)) {
                        $app->log->debug("Registration:: {$registration->id} precisando de DESEMPATE");
                    } else {
                        continue;
                    }
                }

                $committee_valuers_count = $valuers_registrations_count[$committee_name];

                // ordena os usuarios pelos que tem menos avaliações na comissão
                usort($users, fn($u1, $u2) => $committee_valuers_count[$u1->id] <=> $committee_valuers_count[$u2->id]);

                // adiciona os avaliadores da comissão na inscrição
                foreach($users as $user) {
                    $checks_count++;

                    // se a comissão tem limite de avaliadores por inscrição e esse limite já foi atingido, não adiociona
                    $max_valuers = $valuers_per_registration->$committee_name ?? null;
                    if($max_valuers && $registration_valuers_count[$registration->id][$committee_name] >= $max_valuers) {
                        continue;
                    }

                    // se o usuário já é avaliador da inscrição, não precisa adicionar
                    if(isset($result[$registration->id][$user->id])) {
                        continue;
                    }

                    // se o usuário está na lista de exclusão, não adiciona
                    if(in_array($user->id, $registration->valuers_exceptions_list->exclude)) {
                        continue;
                    }

                    $registration_entity = $registration_entity ?: $repo->find($registration->id);

                    if(!$this->canUserBeValuer($registration_entity, $user)) {
                        continue;
                    }

                    // adiciona o usuário na comissão
                    $result[$registration->id][$user->id] = $committee_name;

                    // atualiza o número de avaliações do usuário
                    $valuers_registrations_count[$committee_name][$user->id]++;

                    // incrementa o número de avaliações que a inscrição tem por comissão
                    $registration_valuers_count[$registration->id][$committee_name]++;

                    if($app->config['app.log.evaluations']) {
                        $app->log->debug("Registration: {$registration->number} - Comitê: $committee_name | User: {$user->id} | Count: {$valuers_registrations_count[$committee_name][$user->id]}");
                    }
                }
            }

            $app->em->clear();
            $registration_entity = null;
        }

        file_put_contents($log_filename, i::__('Salvando distribuição'));

        foreach($result as $registraion_id => $valuers) {
            $conn->update('registration', ['valuers' => json_encode($valuers)], ['id' => $registraion_id]);
        } 

        // atualiza os resumos das avaliações
        $evaluationMethodConfiguration = $opportunity->evaluationMethodConfiguration;
        $app->mscache->delete($evaluationMethodConfiguration->summaryCacheKey);

        if($app->config['app.log.evaluations']) {
            file_put_contents($log_filename, i::__('Atualizando o resumo de avaliações da fase'));

            $app->log->debug("Atualizando o resumo de avaliações da fase {$evaluationMethodConfiguration->name} ({$evaluationMethodConfiguration->id})");

        }
        $evaluationMethodConfiguration->getSummary(true);

        /** @var EvaluationMethodConfigurationAgentRelation[] */
        $relations = $evaluationMethodConfiguration->getAgentRelations();
        foreach($relations as $relation) {
            file_put_contents($log_filename, sprintf(i::__('Atualizando o resumo do avaliador %s da comissão %s'), $relation->agent->name, $relation->group));
            $relation->updateSummary();
        }

        if($app->config['app.log.evaluations']) {
            $app->log->debug("Redistribuição de inscrições finalizada em " . round(microtime(true) - $start_time, 2) . " segundos");
        }

        file_put_contents($log_filename, '');
    }

    /**
     * Verifica se a inscrição deve ser avaliada por um determinado comitê
     * @param mixed $registration 
     * @param string $committee_name 
     * @return bool 
     */
    public function mustBeEvaluatedByCommittee(EvaluationMethodConfiguration $evaluation_config, Registration $registration, string $committee_name): bool {
        $can = true;

        $global_filter_configs = $evaluation_config->fetchFields;
        $global_filter_configs = (array) ($global_filter_configs->$committee_name ?? []);

        if ( $categories = $global_filter_configs['category'] ?? null) {
            unset($global_filter_configs['category']);
            if(!$this->canEvaluateRegistrationCategory($registration, $categories)) {
                $can = false;
            }
        }

        if ( $ranges = $global_filter_configs['range'] ?? null) {
            unset($global_filter_configs['range']);
            if(!$this->canEvaluateRegistrationRange($registration, $ranges)) {
                $can = false;
            }
        }

        if ( $proponent_types = $global_filter_configs['proponentType'] ?? null) {
            unset($global_filter_configs['proponentType']);
            if(!$this->canEvaluateRegistrationProponentType($registration, $proponent_types)) {
                $can = false;
            }
        }

        if($global_filter_configs) {
            if(!$this->canEvaluateRegistrationFields($registration, $global_filter_configs)) {
                $can = false;
            }
        }

        return $can;
    }

    /**
     * Verifica se a categoria da inscrição deve ser avaliada pelo filtro
     * @param Registration $registration 
     * @param array $filter_configuration 
     * @return bool 
     */
    public function canEvaluateRegistrationCategory(Entities\Registration $registration, array $filter_configuration): bool {
        foreach($filter_configuration as $cat){
            $cat = trim($cat);
            if(strtolower((string)$registration->category) === strtolower($cat)){
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se a faixa da inscrição deve ser avaliada pelo filtro
     * @param Registration $registration 
     * @param array $filter_configuration 
     * @return bool 
     */
    public function canEvaluateRegistrationRange(Entities\Registration $registration, array $filter_configuration): bool {
        foreach($filter_configuration as $cat){
            $cat = trim($cat);
            if(strtolower((string)$registration->range) === strtolower($cat)){
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o tipo de proponente da inscrição deve ser avaliada pelo filtro
     * @param Registration $registration 
     * @param array $filter_configuration 
     * @return bool 
     */
    public function canEvaluateRegistrationProponentType(Entities\Registration $registration, array $filter_configuration): bool {
        foreach($filter_configuration as $cat){
            $cat = trim($cat);
            if(strtolower((string)$registration->proponentType) === strtolower($cat)){
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o número da inscrição deve ser avaliada pelo filtro por número
     * @param Registration $registration 
     * @param string $filter_configuration 
     * @return bool 
     */
    public function canEvaluateRegistrationNumber(Entities\Registration $registration, string $filter_configuration) {
        $can = true;

        if(preg_match("#([0-9]+) *[-] *([0-9]+)*#", $filter_configuration, $matches)){
            $s1 = $matches[1];
            $s2 = $matches[2];
            
            $len = max([strlen($s1), strlen($s2)]);
            
            $fin = substr($registration->number, -$len);
            
            if(intval($s2) == 0){ // "00" => "100"
                $s2 = "1$s2";
            }
            if($fin < $s1 || $fin > $s2){
                $can = false;
            }
        }

        return $can;
    }

    public function canEvaluateRegistrationFields(Entities\Registration $registration, array $filter_configuration): bool {
        $can = true;
        /** @var Opportunity $opportunity */
        $opportunity = $registration->opportunity;
        $opportunity->registerRegistrationMetadata();
        
        foreach($filter_configuration as $field_name => $values){
            $found_field = false;
            foreach($values as $val) {
                $val = trim($val);
                
                if(strtolower((string)$registration->metadata[$field_name]) === strtolower($val)){
                    $found_field = true;
                }
            }

            if($values && !$found_field){
                $can = false;
            }
        }

        return $can;
    }

    public function canUserEvaluateRegistration(Entities\Registration $registration, User|GuestUser $user){
        if($user->is('guest')){
            return false;
        }

        $valuers = $registration->valuers;

        return isset($valuers[$user->id]);
    }
    
    public function canUserBeValuer(Entities\Registration $registration, User|GuestUser $user): bool {
        $app = App::i();

        if($user->is('guest')){
            return false;
        }

        $cache_key = __METHOD__ . " : $registration -> $user";
        if($app->rcache->contains($cache_key)){
            return $app->rcache->fetch($cache_key);
        }

        $evaluation_config = $registration->evaluationMethodConfiguration;

        $agent_relations = [];
        
        foreach($evaluation_config->getRelatedAgents(return_relations: true) as $relations) {
            $agent_relations = array_merge($agent_relations, $relations);
        }

        $agent_relations = array_filter($agent_relations, function($ar) use($user) {
            return $ar->agent->user->equals($user);
        });

        $is_same_as_evaluator = false;
        $has_global_filter_configs = false;
        foreach($agent_relations as $ar) {
            $config = $evaluation_config->fetchFields->{$ar->group} ?? (object) [];
            foreach($config as $values) {
                if(count($values) > 0) {
                    $has_global_filter_configs = true;
                }
            }
            $config = $evaluation_config->valuersPerRegistration->{$ar->group} ?? null;
            if(!empty((array) $config)) {
                $has_global_filter_configs = true;
            }

            if($registration->owner->id == $ar->agent->id) {
                $is_same_as_evaluator = true;
            }
        }

        if (
            $is_same_as_evaluator || 
            (
                empty($evaluation_config->fetch->{$user->id}) && 
                empty($evaluation_config->fetchCategories->{$user->id}) && 
                empty($evaluation_config->fetchRanges->{$user->id}) && 
                empty($evaluation_config->fetchProponentTypes->{$user->id}) && 
                empty($evaluation_config->fetchSelectionFields->{$user->id}) && 
                !$has_global_filter_configs
            )
        ) {
            return false;
        }

        if($can = $evaluation_config->canUser('@control', $user)){
            $fetch = [];
            $config_fetch = (array) $evaluation_config->fetch;
            $config_fetchCategories = (array) $evaluation_config->fetchCategories;
            $config_ranges = (array) $evaluation_config->fetchRanges;
            $config_proponent_types = (array) $evaluation_config->fetchProponentTypes;
            $config_selection_fields = (array) $evaluation_config->fetchSelectionFields;
            $global_filter_configs = (array) $evaluation_config->fetchFields;
            
            $relations = $evaluation_config->agentRelations;

            if(is_array($global_filter_configs)) {
                $global_config_categories = [];
                $global_config_ranges = [];
                $global_config_proponent_types = [];
                $global_config_selection_fields = [];

                foreach($relations as $relation) {
                    if($relation->agent->user->equals($user)) {
                        $committee_config = $global_filter_configs[$relation->group] ?? (object) [];

                        $global_config_categories = array_merge($global_config_categories, (array) ($committee_config->category ?? []));

                        $global_config_ranges = array_merge($global_config_ranges, (array) ($committee_config->range ?? []));

                        $global_config_proponent_types = array_merge($global_config_proponent_types, (array) ($committee_config->proponentType ?? []));

                        foreach ($committee_config as $key => $value) {
                            if (!in_array($key, ['category', 'range', 'proponentType', 'distribution'])) {
                                $global_config_selection_fields[$key] = array_merge($global_config_selection_fields[$key] ?? [], (array) $value);
                            }
                        }
                    }
                }

                if(!empty($global_config_categories)) {
                    $config_fetchCategories = [$user->id => $global_config_categories];
                }

                if(!empty($global_config_ranges)) {
                    $config_ranges = [$user->id => $global_config_ranges];
                }

                if(!empty($global_config_proponent_types)) {
                    $config_proponent_types = [$user->id => $global_config_proponent_types];
                }

                if(!empty($global_config_selection_fields)) {
                    $config_selection_fields = [$user->id => $global_config_selection_fields];
                }
            }

            if(is_array($config_fetch)){
                foreach($config_fetch as $id => $val){
                    $fetch [(int)$id] = $val;
                }
            }
            $fetch_categories = [];
            if(is_array($config_fetchCategories)){
                foreach($config_fetchCategories as $id => $val){
                    $fetch_categories [(int)$id] = $val;
                }
            }

            $fetch_selection_fields = [];
            if(is_array($config_selection_fields)) {
                foreach($config_selection_fields as $id => $fields) {
                    foreach($fields as $field => $val) {
                        $fetch_selection_fields [(int)$id][$field] = $val;
                    }
                }
            }

            $fetch_ranges = [];
            if(is_array($config_ranges)){
                foreach($config_ranges as $id => $val){
                    $fetch_ranges [(int)$id] = $val;
                }
            }

            $fetch_proponent_types = [];
            if(is_array($config_proponent_types)){
                foreach($config_proponent_types as $id => $val){
                    $fetch_proponent_types [(int)$id] = $val;
                }
            }

            // verifica permissão de avaliação por número da inscrição
            if ($ufetch = $fetch[$user->id] ?? false){
                if(!$this->canEvaluateRegistrationNumber($registration, $ufetch)){
                    $can = false;
                }
            }

            // verifica permissão de avaliação por categoria
            if ($ucategories = $fetch_categories[$user->id] ?? false){
                if(!$this->canEvaluateRegistrationCategory($registration, $ucategories)){
                    $can = false;
                }
            }

            // verifica permissão de avaliação por faixa
            if ($uranges = $fetch_ranges[$user->id] ?? false){
                if(!$this->canEvaluateRegistrationRange($registration, $uranges)){
                    $can = false;
                }
            }

            // verifica permissão de avaliação por tipo de proponente
            if ($uproponent_types = $fetch_proponent_types[$user->id] ?? false){
                if(!$this->canEvaluateRegistrationProponentType($registration, $uproponent_types)){
                    $can = false;
                }
            }

            // verifica permissão de avaliação por campos de seleção
            if ($uselection_fields = $fetch_selection_fields[$user->id] ?? false){
                if(!$this->canEvaluateRegistrationFields($registration, $uselection_fields)){
                    $can = false;
                }
            }
        }

        $app->rcache->save($cache_key, $can);

        return $can;
    }

    function canUserViewConsolidatedResult(Entities\Registration $registration){
        $opp = $registration->opportunity;

        if($opp->publishedRegistrations || $opp->canUser('@control')){
            return true;
        } else {
            return false;
        }
    }

    function getEvaluationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-form";
    }

    public function getEvaluationSummary($registration) {
        $app = App::i();

        $result = [];
        if($evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration])){
            $consolidated_result =  $this->_getConsolidatedResult($registration);
            $result['consolidated_result'] = $consolidated_result;
            $result['type'] = $this->getName();
            $result['value_to_string'] = $this->valueToString($consolidated_result);

            foreach($evaluations as $evaluation){
                $data = [
                    'id' => $evaluation->id,
                    'evaluation_data' => $evaluation->evaluationData,
                    'avaluator_id' => $evaluation->user->profile->id,
                    'avaluator_name' => $evaluation->user->profile->name,
                    'status' => $evaluation->getResultString(),
                ];

                $result[] = (object)$data;
            }
        }

        return $result;
    }

    function getEvaluationViewPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-view";
    }

    function getEvaluationFormInfoPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-info";
    }
    
    function getConfigurationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--configuration-form";
    }

    function register(){
        $app = App::i();

        $def = new Definitions\EvaluationMethod($this);

        $app->registerEvaluationMethod($def);

        $type = new Definitions\EntityType('MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug(), $this->getName());

        $app->registerEntityType($type);

        $this->_register();

        $self = $this;

        $app->hook('view.includeAngularEntityAssets:after', function() use($self){
            $self->enqueueScriptsAndStyles();
        });
        
        $this->registerEvaluationMethodConfigurationMetadata('infos', [
            'label' => i::__('Textos informativos para os avaliadores'),
            'type' => 'json',
            'default' => '{}'
        ]);
    }
    
    function registerEvaluationMethodConfigurationMetadata($key, array $config){
        $app = App::i();

        $metadata = new Definitions\Metadata($key, $config);

        $app->registerMetadata($metadata, 'MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug());
    }

    function usesEvaluationCommittee(){
        return true;
    }
    
    public function useCommitteeGroups(): bool {
        return true;
    }

    public function evaluateSelfApplication(): bool {
        return true;
    }

    public function jsonSerialize(): array {
        return [];
    }
}
