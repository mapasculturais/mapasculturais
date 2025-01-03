<?php
namespace MapasCulturais;

use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use MapasCulturais\Entities;
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

        $committee = []; 
        foreach($evaluation_config->getAgentRelations(null, false) as $relation) {
            if($relation->status == EvaluationMethodConfigurationAgentRelation::STATUS_ENABLED) {
                $committee[$relation->group] = $committee[$relation->group] ?? [];
                $committee[$relation->group][] = $relation->agent->user;
            }
        }

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

        $registration_committee = $registration->getCommittees(true);
        
        $valuer_users = [];
        foreach($registration_committee as $users) {
            $valuer_users = array_merge($valuer_users, $users);
        }

        // não há avaliadores para a inscrição
        if(count($valuer_users) === 0) {
            return false;
        }

        $criteria = [
            'registration' => $registration, 
            'status' => RegistrationEvaluation::STATUS_SENT,
            'user' => $valuer_users,
            'isTiebreaker' => false
        ];
        
        $evaluations =  $app->repo('RegistrationEvaluation')->findBy($criteria);


        // se o total de avaliadores por inscrição dentro de cada comissão não está preenchido, 
        // então a inscrição AINDA NÃO precisa de desempate

        foreach($registration->evaluationMethodConfiguration->valuersPerRegistration as $committee => $num) {
            if($committee == '@tiebreaker') {
                continue;
            }

            $users = $registration_committee[$committee] ?? [];

            foreach($evaluations as $evaluation) {
                foreach($users as $user) {
                    if($evaluation->user->equals($user)) {
                        $num--;
                    }
                }
            }

            if($num > 0) {
                return false;
            }
        }

        $results = [];

        foreach($registration_committee as $group => $users) {
            $user_ids = array_map(fn($user) => $user->id, $users);

            $group_evaluations = array_filter($evaluations, fn($e) => in_array($e->user->id, $user_ids));

            $results[$group] = $this->_getConsolidatedResult($registration, $group_evaluations);
        }


        // se há mais que um valor no array, então há divergência
        if(count(array_unique($results)) > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function redistributeRegistrations(Entities\Opportunity $opportunity) {
        $app = App::i();
        $evaluation_config = $opportunity->evaluationMethodConfiguration;
        $conn = $app->em->getConnection();

        $registrations_valuers = [];
        
        $committee = $this->getCommitteeGroups($opportunity->evaluationMethodConfiguration);

        // coloca o comitê de desempate no final do array
        if(isset($committee['@tiebreaker'])) {
            $tiebreaker_committee = $committee['@tiebreaker'];
            unset($committee['@tiebreaker']);
            $committee['@tiebreaker'] = $tiebreaker_committee;
        }

        $must_enqueue_evaluation_config = false;

        $non_tiebreaker_valuers = [];
        
        foreach($committee as $group => $committee_users) {
            $valuers_per_registration = (int) ($evaluation_config->valuersPerRegistration->$group ?? 0);
            $ignore_started_evaluations = $evaluation_config->ignoreStartedEvaluations->$group ?? false;

            if(!$valuers_per_registration) {
                $must_enqueue_evaluation_config = true;
                continue;
            }

            $committee_user_ids = array_map(fn($user) => $user->id, $committee_users);

            $_user_ids = implode(',', $committee_user_ids);

            /** 
             * Obtém a lista de inscrições que têm menos avaliações feitas do que precisam ter
             */
            $registration_evaluations = $conn->fetchAllAssociative("
                SELECT 
                    r.id, 
                    r.valuers_exceptions_list,
                    v.user_id,
                    count(e.id) AS num 
                FROM 
                    registration r 
                LEFT JOIN 
                    registration_evaluation e ON e.registration_id = r.id AND e.user_id IN ($_user_ids)
                LEFT JOIN 
                    registration_evaluation v ON v.registration_id = r.id AND v.user_id IN ($_user_ids)
                
                WHERE 
                    opportunity_id = {$opportunity->id} AND
                    r.status = 1

                GROUP BY r.id, v.id
                ORDER BY num ASC
            ");


            /**
             * Processa a lista agrupando os avaliadores que já avaliaram a inscrição
             */
            $result = [];
            $valuers_evaluated_registrations = [];
            foreach($registration_evaluations as $r) {
                
                $result[$r['id']] = $result[$r['id']] ?? (object) [
                    'id' => $r['id'],
                    'valuers' => [],
                    'valuers_exceptions_list' => json_decode($r['valuers_exceptions_list'])
                ];

                if($r['user_id']) {
                    if($group == '@tiebreaker' && in_array($r['user_id'], $non_tiebreaker_valuers)) {
                        continue;
                    }
                    $result[$r['id']]->valuers[] = $r['user_id'];
                    $valuers_evaluated_registrations[$r['user_id']] = $valuers_evaluated_registrations[$r['user_id']] ?? 0;
                    $valuers_evaluated_registrations[$r['user_id']]++;
                }
            }

            $valuers = [];

            /** Distribui as inscrições */
            foreach ($committee_users as $user) {
                if($group == '@tiebreaker' && in_array($user->id, $non_tiebreaker_valuers)) {
                    continue;
                }
                if($ignore_started_evaluations) {
                    $num = 0;
                } else {
                    $num = $valuers_evaluated_registrations[$user->id] ?? 0;
                }

                if($group != '@tiebreaker') {
                    $non_tiebreaker_valuers[] = $user->id;
                }

                $valuers[] = (object) [
                    'count' => $num,
                    'user' => $user
                ];
            }

            foreach($result as &$reg) {
                foreach($valuers as &$valuer) {
                    $user = $valuer->user;

                    if(count($reg->valuers) >= $valuers_per_registration || in_array($user->id, $reg->valuers)) {
                        continue;
                    }

                    /** @var Registration $registration */
                    $registration = $app->repo('Registration')->find($reg->id);

                    if($group == '@tiebreaker' && !$this->registrationNeedsTiebreaker($registration)) {
                        continue;
                    }

                    if($this->canUserEvaluateRegistration($registration, $user, skip_exceptions: true, skip_valuers_limit: true)) {
                        $reg->valuers[] = $user->id;
                        $valuer->count++;
                    }
                }

                usort($valuers, fn($u1, $u2) => $u1->count <=> $u2->count);
            }

            foreach($result as $r) {
                $registrations_valuers[$r->id] = $registrations_valuers[$r->id] ?? (object) [
                    'valuers' => [],
                    'valuers_exceptions_list' => $r->valuers_exceptions_list
                ];
                $registrations_valuers[$r->id]->valuers = array_unique(array_merge($registrations_valuers[$r->id]->valuers, $r->valuers));
            }
        }

        foreach($registrations_valuers as $registration_id => $r) {
            $app->log->debug(print_r($r->valuers, true));
            $users = array_merge($r->valuers_exceptions_list->include, $r->valuers_exceptions_list->exclude, $r->valuers);
            $r->valuers_exceptions_list->include = $r->valuers;
            $json = json_encode ($r->valuers_exceptions_list);

            $app->log->debug("$registration_id  $json");
            $conn->update('registration', ['valuers_exceptions_list' => $json], ['id' => $registration_id]);
            $r = $app->repo('Registration')->find($registration_id);
            $r->enqueueToPCacheRecreation($users);
        }

        $app->persistPCachePendingQueue();
    }

    public function canUserEvaluateRegistration(Entities\Registration $registration, User|GuestUser $user, $skip_exceptions = false, $skip_valuers_limit = false){
        $app = App::i();

        if($user->is('guest')){
            return false;
        }

        $cache_key = "$registration -> $user";
        if(!$skip_exceptions && !$skip_valuers_limit && $app->rcache->contains($cache_key)){
            return $app->rcache->fetch($cache_key);
        }

        $evaluation_config = $registration->evaluationMethodConfiguration;
        $valuers_per_registration_config = $evaluation_config->valuersPerRegistration;

            
        $agent_relations = $app->repo('EvaluationMethodConfigurationAgentRelation')->findBy([
            'owner' => $evaluation_config,
            'agent' => $user->profile
        ]);

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

        
        /**
         * Se tem configuração de limite de avaliadores por inscrição, 
         * a regra de distribuição não deve ser considerada pois só valerá
         * o que estiver no valuersIncludeList da inscrição
         */

        // encontra em qual comitê de avaliação o usuário está
        $committee_group = null;
        $has_limit = false;
        if(!$skip_valuers_limit) {
            $committee = $this->getCommitteeGroups($evaluation_config);
            
            foreach($committee as $group => $valuers) {
                foreach($valuers as $valuer) {
                    if($user->equals($valuer)) {
                        $committee_group = $group;
                        if($valuers_per_registration_config->$committee_group ?? false) {
                            $has_limit = true;
                        }
                    }
                }
            }
        }

        if($can = $evaluation_config->canUser('@control', $user) && !$has_limit){
            $fetch = [];
            $config_fetch = is_array($evaluation_config->fetch) ? $evaluation_config->fetch : (array) $evaluation_config->fetch;
            $config_fetchCategories = is_array($evaluation_config->fetchCategories) ? $evaluation_config->fetchCategories : (array) $evaluation_config->fetchCategories;
            $config_ranges = is_array($evaluation_config->fetchRanges) ? $evaluation_config->fetchRanges : (array) $evaluation_config->fetchRanges;
            $config_proponent_types = is_array($evaluation_config->fetchProponentTypes) ? $evaluation_config->fetchProponentTypes : (array) $evaluation_config->fetchProponentTypes;
            $config_selection_fields = is_array($evaluation_config->fetchSelectionFields) ? $evaluation_config->fetchSelectionFields : (array) $evaluation_config->fetchSelectionFields;
            $global_filter_configs = isset($evaluation_config->fetchFields) && is_array($evaluation_config->fetchFields) ? $evaluation_config->fetchFields : (array) $evaluation_config->fetchFields;
            
            $relations = $registration->opportunity->evaluationMethodConfiguration->agentRelations;

            if(is_array($global_filter_configs)) {
                $global_config_categories = [];
                $global_config_ranges = [];
                $global_config_proponent_types = [];
                $global_config_selection_fields = [];

                foreach($relations as $relation) {
                    if($relation->agent->id == $user->profile->id) {
                        $committee_config = $global_filter_configs[$relation->group] ?? (object) [];

                        if(!empty($committee_config->category)) {
                            $global_config_categories = array_merge($global_config_categories, (array) $committee_config->category);
                        }

                        if(!empty($committee_config->range)) {
                            $global_config_ranges = array_merge($global_config_ranges, (array) $committee_config->range);
                        }

                        if(!empty($committee_config->proponentType)) {
                            $global_config_proponent_types = array_merge($global_config_proponent_types, (array) $committee_config->proponentType);
                        }

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

            if(isset($fetch[$user->id])){
                $ufetch = $fetch[$user->id];
                if(preg_match("#([0-9]+) *[-] *([0-9]+)*#", $ufetch, $matches)){
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
            }

            if(isset($fetch_categories[$user->id])){
                $ucategories = $fetch_categories[$user->id];
                if($ucategories){
                    if(!is_array($ucategories)) {
                        $ucategories = explode(';', $ucategories);
                    }

                    if($ucategories){
                        $found = false;

                        foreach($ucategories as $cat){
                            $cat = trim($cat);
                            if(strtolower((string)$registration->category) === strtolower($cat)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }

            if(isset($fetch_ranges[$user->id])){
                $uranges = $fetch_ranges[$user->id];
                if($uranges){
                    if(!is_array($uranges)) {
                        $uranges = explode(';', $uranges);
                    }

                    if($uranges){
                        $found = false;

                        foreach($uranges as $ran){
                            $ran = trim($ran);
                            if(strtolower((string)$registration->range) === strtolower($ran)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }
            
            if(isset($fetch_proponent_types[$user->id])){
                $uproponet_types = $fetch_proponent_types[$user->id];
                if($uproponet_types){
                    if(!is_array($uproponet_types)) {
                        $uproponet_types = explode(';', $uproponet_types);
                    }

                    if($uproponet_types){
                        $found = false;

                        foreach($uproponet_types as $ran){
                            $ran = trim($ran);
                            if(strtolower((string)$registration->proponentType) === strtolower($ran)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }
            
            if(isset($fetch_selection_fields[$user->id])){
                $uselection_fields = $fetch_selection_fields[$user->id];
                if($uselection_fields){
                    if($uselection_fields){
                        $found_selection_fields = false;
                        
                        /** @var Opportunity $opportunity */
                        $opportunity = $registration->opportunity;
                        $opportunity->registerRegistrationMetadata();
                        $fields = $opportunity->registrationFieldConfigurations;

                        $field_name = [];
                        foreach($fields as $field) {
                            $field_name[$field->title] = $field->fieldName;
                        }

                        foreach($uselection_fields as $key => $values){
                            foreach($values as $val) {
                                $val = trim($val);
                                
                                if(strtolower((string)$registration->metadata[$field_name[$key]]) === strtolower($val)){
                                    $found_selection_fields = true;
                                }
                            }
                        }

                        $can = $found_selection_fields ? true : false;
                    }
                }
            }
        }

        if(!$skip_exceptions) {
            $can = $can || in_array($user->id, (array) $registration->valuersIncludeList);
            $can = $can && !in_array($user->id, (array) $registration->valuersExcludeList);
            $app->rcache->save($cache_key, $can);
        }
        
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
