<?php
namespace EvaluationMethodTechnical;

use Doctrine\ORM\Exception\NotSupported;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Traits;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * 
 * 
        // REFATORAÇÃO
// respeitar a proporção definida de vagas para cada região dentro das faixas
// respeitar a proporção definida de vagas para cada tipo de cota dentro das faixas
// respeitar a proporção definida de vagas para cada tipo de cota dentro das regiões


// implementar função que retorna se ainda ha vaga na faixa e região de uma inscrição

// cria listas de inscrições por faixa, por região e por tipo de cota, ordenadas por score
// cria listas vazias para cada faixa, região e tipo de cota
    // se estiver configurado para considerar os proponentes classificados na ampla concorrência como cotistas

    // se estiver configurado para NÃO considerar os proponentes classificados na ampla concorrência como cotistas

    
 * @property-read array $quotaFields Lista de campos utilizados nas cotas
 * @property-read array $tiebreakerFields Lista de campos utilizados nos critérios de desempate
 * @property-read array $fields Lista de campos utilizados
 * @property-read array $registrationsForQuotaSorting lista de inscrições para a ordenação de cotas
 * 
 * @package EvaluationMethodTechnical
 */
class Quotas {
    use Traits\MagicGetter,
        Traits\MagicSetter;

    protected Opportunity $firstPhase;
    protected Opportunity $phase;
    protected EvaluationMethodConfiguration $evaluationConfig;

    protected int $vacancies;
    
    protected array $tiebreakerConfig;

    protected array $rangesConfig;
    protected object $geoQuotaConfig;
    protected object $quotaConfig;

    protected array $selectedGlobal = [];
    protected array $selectedByQuotas = [];
    protected array $selectedByGeo = [];
    protected array $selectedByRanges = [];
        
    function __construct(int $phase_id) {
        $app = App::i();
        
        $this->phase = $app->repo('Opportunity')->find($phase_id);
        $this->firstPhase = $this->phase->firstPhase;
        $this->evaluationConfig = $this->phase->evaluationMethodConfiguration;

        $this->vacancies = $this->firstPhase->vacancies;

        $this->tiebreakerConfig = $this->evaluationConfig->tiebreakerCriteriaConfiguration ?: [];
        $this->quotaConfig = $this->evaluationConfig->quotaConfiguration ?: (object) ['rules' => (object) []];
        $this->geoQuotaConfig = $this->evaluationConfig->geoQuotaConfiguration ?: (object) ['distribution' => (object) [], 'geoDivision' => null];
        $this->geoQuotaConfig->distribution = (object) $this->geoQuotaConfig->distribution;

        $registration_ranges = $this->first_phase->registrationRanges ?: [];
        foreach($registration_ranges as $range) {
            $this->rangesConfig[$range['label']] = $range['limit'];
        }

    }

    /**
     * Retorna os campos utilizados nas cotas
     * 
     * @return array 
     */
    protected function getQuotaFields(): array {
        $fields = ['appliedForQuota'];

        foreach($this->quotaConfig->rules as $rule) {
            foreach($rule->fields as $field) {
                $fields[] = $field->fieldName;
            }
        }

        $fields = array_values(array_unique($fields));

        return $fields;
    }

    /**
     * Retorna os campos utilizados nos critérios de desempate
     * 
     * @return array 
     */
    protected function getTiebreakerFields(): array {
        $fields = [];

        foreach($this->tiebreakerConfig as $rule) {
            if(str_starts_with($rule->criterionType, 'field_' ) ) {
                $fields[] = $rule->criterionType;
            } else {
                // @TODO colocar o metadado que salvará a consolidação da avaliação técnica
            }
        }

        $fields = array_values(array_unique($fields));
        
        return $fields;
    }

    /**
     * Retorna os campos utilizados
     * @return array 
     */
    protected function getFields(): array {
        $quotaFields = $this->quotaFields;
        $tiebreakerFields = $this->tiebreakerFields;
        $fields = array_unique([...$quotaFields, ...$tiebreakerFields]);

        return $fields;
    }

    /**
     * Retorna lista de inscrições para a ordenação das inscrições considerando as cotas, 
     * contendo todos os campos que serão utilizados.
     * 
     * @param array $params 
     * @return array 
     * @throws InvalidArgumentException 
     */
    function getRegistrationsForQuotaSorting(array $params = null): array {
        $app = App::i();
                
        $use_cache = $app->config['app.useQuotasCache'];
        
        if($params) {
            unset(
                $params['@select'], 
                $params['@order'], 
                $params['@limit'], 
                $params['@page'],
                $params['opportunity'],
            );
        } else {
            $params = [];
        }
        
        $cache_key = "{$this->phase}:quota-registrations:" . md5(serialize($params));
        
        if($use_cache && $app->cache->contains($cache_key)){
            return $app->cache->fetch($cache_key);
        }

        $result = $app->controller('opportunity')->apiFindRegistrations($this->phase, [
            '@select' => implode(',', ['number,range,proponentType,agentsData,consolidatedResult,eligible,score', ...$this->fields]),
            '@order' => 'score DESC',
            '@quotaQuery' => true,
            ...$params
        ]);

        $registrations = array_map(function ($reg) {
            return (object) $reg; 
        }, $result->registrations);

        if($use_cache){
            $app->cache->save($cache_key, $registrations, $app->config['app.quotasCache.lifetime']);
        }

        return $registrations;
    }


    /**
     * Retorna a região de uma inscrição
     * 
     * @param mixed $registration 
     * @return string 
     */
    function getRegistrationRegion($registration): string {
        $app = App::i();

        $opportunity_proponent_types = $$this->firstPhase->registrationProponentTypes;
        $proponent_types2agents_map = $app->config['registration.proponentTypesToAgentsMap'];
        
        $proponent_type = $registration->proponentType;

        if(!$opportunity_proponent_types) {
            $agent_data = $registration->agentsData['owner'];
        } else {
            $agent_key = $proponent_types2agents_map[$proponent_type] ?? null;
            $agent_data = $registration->agentsData[$agent_key] ?? null;
        }

        // ISSO NÃO DEVERIA SER POSSÍVEL
        if(!$agent_data) {
            $agent_data = $registration->agentsData['owner'];
        }

        $meta = $this->geoQuotaConfig->geoDivision;
        $region =  $agent_data[$meta] ?? '';

        if(isset($this->geoQuotaConfig->distribution->$region)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    /**
     * Retorna lista de inscrições ordenadas pela classificação final considerando as cotas
     * 
     * @param mixed $params 
     * @return mixed 
     * @throws NotSupported 
     * @throws InvalidArgumentException 
     */
    public function getRegistrationsOrderByScoreConsideringQuotas($params = null) {
        $exclude_ampla_concorrencia = !$this->firstPhase->considerQuotasInGeneralList;
        
        /** ===  INICIALIZANDO AS LISTAS === */
        // cotas
        $total_quota = 0;
        foreach($this->quotaConfig->rules as $rule) {
            $rule_id = $this->generateRuleId($rule);
            $this->selectedByQuotas[$rule_id] = $this->selectedByQuotas[$rule_id] ?? [];
            $total_quota += $rule->vacancies;
        }

        $total_ampla_concorrencia = $this->vacancies - $total_quota;

        // distribuição geográfica
        $total_distribution = 0;
        foreach($this->geoQuotaConfig->distribution as $region => $num) {
            if($num > 0){
                $total_distribution += $num;
                $this->selectedByGeo[$region] = $this->selectedByGeo[$region] ?? [];
            } else {
                unset($this->geoQuotaConfig->distribution->$region);
            }
        }

        $this->geoQuotaConfig->distribution->OTHERS = $this->vacancies - $total_distribution;
    
        // distribuição nas faixas
        foreach($this->rangesConfig as $range => $num) {
            $this->selectedByRanges[$range] = $this->selectedByRanges[$range] ?? [];
        }

        $registrations = $this->getRegistrationsForQuotaSorting($params);
        $registrations = $this->tiebreaker($registrations);
        
        /** === POPULANDO AS LISTAS === */
        // primeiro preenche as cotas
        foreach($this->quotaConfig->rules as $rule) {

            $rule_id = $this->generateRuleId($rule);
            
            foreach($registrations as $i => &$reg) {
                if($exclude_ampla_concorrencia && $i < $total_ampla_concorrencia) {
                    continue;
                }

                // se a pessoa não é elegível, ela não conta nas cotas (pode ser pq falou que não quer ser cotista ou pq nenhum critério configurado bateu)
                if(!$reg->eligible) {
                    continue;
                }

                // para impedir que uma inscrição que se enquadre em mais de 1 critério ocupe 2 vagas
                if(in_array($reg, $this->selectedGlobal)) {
                    continue;
                }
                
                $quota_count = count($this->selectedByQuotas[$rule_id]);
                
                $region = $this->getRegistrationRegion($reg);

                /** @todo verificar se não excedeu o máximo de vagas em cada região ou faixa*/
                foreach($rule->fields as $field){
                    $field_name = $field->fieldName;

                    if($quota_count < $rule->vacancies && in_array($reg->$field_name, $field->eligibleValues)) {
                        $selected_by_quotas[$rule_id][] = &$reg;
                        $selected_global[] = &$reg;

                        $selected_by_geo[$region][] = &$reg;
                        $this->selectedByRanges[$reg->range][] = &$reg;
                    }
                }
            }
        }

        foreach($registrations as &$reg) {
            if(in_array($reg, $selected_global)) {
                continue;
            }

            $selected = true;
            
            $region = $this->getRegistrationRegion($reg);
            $geo_count = count($this->selectedByGeo[$region] ?? []);
            if(isset($this->geoQuotaConfig->distribution->$region) && $geo_count >= $this->geoQuotaConfig->distribution->$region) {
                // var_dump([$region, $geo_quota_config->distribution->$region, $reg->regiao]);
                $selected = false;
            }

            $range = $reg->range;
            $range_count = count($this->selectedByRanges[$range] ?? []);
            if(isset($ranges_config[$range]) && $range_count >= $ranges_config[$range]) {
                $selected = false;
            }

            if($selected) {
                $this->selectedByGeo[$region][] = &$reg;
                $this->selectedByRanges[$range][] = &$reg;
                $selected_global[] = $reg;
            }
        }
        
        $selected_global = $this->tiebreaker($this->selectedGlobal);

        $result = array_values($selected_global);
        
        foreach(array_values($registrations) as &$reg) {
            if(!in_array($reg, $result)){
                $result[] = &$reg;
            }
        }

        return $result;

    }

    protected function generateRuleId($rule) {
        $app = App::i();
        return isset($rule->title) ? $app->slugify($rule->title) : md5(json_encode($rule));
    }


    public function tiebreaker($registrations) {
        $app = App::i();
        $self = $app->modules['EvaluationMethodTechnical']; 
        $tiebreaker_configuration = $this->tiebreakerConfig;

        usort($registrations, function($registration1, $registration2) use($tiebreaker_configuration, $self) {
            $result = $registration2->score <=> $registration1->score;
            if($result != 0) {
                return $result;
            }

            foreach($tiebreaker_configuration as $tiebreaker) {
                $selected = $tiebreaker->selected;
                if($selected !== null && $selected->fieldType == 'select') {
                    $registration1Has = in_array($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = in_array($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                if($selected !== null && in_array($selected->fieldType, ['integer', 'numeric', 'number', 'float', 'currency', 'date'])) {
                    $registration1Has = $registration1->{$tiebreaker->criterionType};
                    $registration2Has = $registration2->{$tiebreaker->criterionType};

                    $result = $registration1Has <=> $registration2Has;

                    if($tiebreaker->preferences == 'smallest') {
                        if ($result !== 0) {
                            return $result;
                        }
                    }

                    if($tiebreaker->preferences == 'largest') {
                        if ($result !== 0) {
                            return -$result;
                        }
                    }
                }

                if($selected !== null && in_array($selected->fieldType, ['multiselect', 'checkboxes'])) {
                    $registration1Has = array_intersect($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = array_intersect($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);

                    $registration1Has = !empty($registration1Has);
                    $registration2Has = !empty($registration2Has);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }
                
                if($selected !== null && in_array($selected->fieldType, ['boolean', 'checkbox'])) {
                    $registration1Has = $registration1->{$tiebreaker->criterionType};
                    $registration2Has = $registration2->{$tiebreaker->criterionType};

                    $result = $registration1Has <=> $registration2Has;

                    if($tiebreaker->preferences == 'marked') {
                        if ($result !== 0) {
                            return -$result;
                        }
                    }

                    if($tiebreaker->preferences == 'unmarked') {
                        if ($result !== 0) {
                            return $result;
                        }
                    }
                }

                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'criterion') {
                    $registration1Has = $self->tiebreakerCriterion($tiebreaker->preferences, $registration1->id);
                    $registration2Has = $self->tiebreakerCriterion($tiebreaker->preferences, $registration2->id);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'sectionCriteria') {
                    $registration1Has = $self->tiebreakerSectionCriteria($tiebreaker->preferences, $registration1->id);
                    $registration2Has = $self->tiebreakerSectionCriteria($tiebreaker->preferences, $registration2->id);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

            }
        });
        
        return $registrations;
    }

    public function tiebreakerCriterion($criteriaId, $registrationId) {
        $app = App::i();
        
        $registration = $app->repo('Registration')->find($registrationId);
        $criteria = $registration->evaluationMethodConfiguration->criteria;

        $findCriteria = [];
        foreach($criteria as $criterion) {
            if($criterion->id === $criteriaId) {
                $findCriteria[] = $criterion;
            }
        }

        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();

        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

        $result = 0;
        foreach ($evaluations as $eval) {
            foreach($eval->evaluationData as $key => $data) {
                foreach($findCriteria as $cri) {
                    if($key === $criteriaId) {
                        $result += $data * $cri->weight;
                    }
                }
            }
        }

        $num = count($evaluations);

        return $num ? number_format($result / $num, 2) : null;
    }

    public function tiebreakerSectionCriteria($sectionId, $registrationId) {
        $app = App::i();
        
        $registration = $app->repo('Registration')->find($registrationId);
        $criteria = $registration->evaluationMethodConfiguration->criteria;

        $findCriteria = [];
        foreach($criteria as $criterion) {
            if($criterion->sid === $sectionId) {
                $findCriteria[] = $criterion;
            }
        }

        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();

        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

        $result = 0;
        foreach ($evaluations as $eval) {
            foreach($eval->evaluationData as $key => $data) {
                foreach($findCriteria as $cri) {
                    if($key === $cri->id) {
                        $result += $data * $cri->weight;
                    }
                }
            }
        }

        return number_format($result, 2);
    }
}