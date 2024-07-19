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

    protected array $tiebreakerConfig;

    protected int $vacancies;
    protected array $selectedGlobal = [];
    
    protected int $quotaVacancies;
    protected array $quotaRules = [];
    protected array $quotaConfig = [];
    protected array $quotaTypes = [];
    protected array $selectedByQuotas = [];

    protected array $rangesConfig = [];
    protected array $rangeNames = [];
    protected array $selectedByRanges = [];
    

    protected string $geoDivision;
    protected object $geoDivisionFields;
    protected array $geoQuotaConfig = [];
    protected array $geoLocations = [];
    protected array $selectedByGeo = [];

    // o grupo é a concatenação da regra da região com faixa
    protected array $registrationsByGroup = [];

    // o grupo é a concatenação da regra da região com faixa e tipo de cota
    protected array $registrationsByQuotaGroup = [];

    protected bool $considerQuotasInGeneralList = false;
        
    function __construct(int $phase_id) {
        $app = App::i();
        
        $this->phase = $app->repo('Opportunity')->find($phase_id);
        $this->firstPhase = $this->phase->firstPhase;
        $this->evaluationConfig = $this->phase->evaluationMethodConfiguration;

        $this->vacancies = $this->firstPhase->vacancies;

        $this->considerQuotasInGeneralList = $this->firstPhase->considerQuotasInGeneralList;

        // proecessa a configuração de cotas
        $this->quotaRules = $this->evaluationConfig->quotaConfiguration->rules ?: [];
        
        $this->quotaVacancies = 0;
        foreach($this->quotaRules as $rule) {
            $quota_type_slug = $this->getQuotaTypeSlugByRule($rule);
            $this->quotaTypes[] = $quota_type_slug;
            $this->selectedByQuotas[$quota_type_slug] = [];
            $this->quotaVacancies += ceil($rule->vacancies);

            $this->quotaConfig[$quota_type_slug] = (object) [
                'vacancies' => $rule->vacancies,
                'percent' => $rule->vacancies / $this->vacancies,
            ];
        }

        // proecessa a configuração de faixas
        $registration_ranges = $this->firstPhase->registrationRanges ?: [];
        foreach($registration_ranges as $range) {
            $range_name = $range['label'];
            $range_vacancies = $range['limit'];

            $this->rangesConfig[$range_name] = (object) [
                'vacancies' => $range_vacancies, 
                'percent' => $range_vacancies / $this->vacancies
            ];

            $this->rangeNames[] = $range_name;
            $this->selectedByRanges[$range_name] = [];
        }

        // processa a configuração de distribuição geográfica
        if($geo_config = $this->evaluationConfig->geoQuotaConfiguration) {
            $geo_config = (object) $geo_config;
            $distribution = (object) $geo_config->distribution;

            $this->geoDivision = $geo_config->geoDivision;
            $this->geoDivisionFields = $geo_config->fields;

            foreach($distribution as $region => $num) {
                $this->geoQuotaConfig[$region] = (object) [
                    'vacancies' => $num,
                    'percent' => $num / $this->vacancies
                ];
                $this->geoLocations[] = $region;
                $this->selectedByGeo[$region] = [];
            }
        }

        // criando grupos
        // 1. distribuição geográfica e faixas ativas
        if($this->geoQuotaConfig && $this->rangesConfig) {
            foreach($this->geoLocations as $region) {
                foreach($this->rangeNames as $range) {
                    $group = "{$region}:{$range}:";
                    $this->registrationsByGroup[$group] = [];
                    foreach($this->quotaTypes as $quota) {
                        $quota_group = "{$region}:{$range}:{$quota}";
                        $this->registrationsByQuotaGroup[$quota_group] = [];
                    }
                }
            }
        }
        // 2. distribuição geográfica ativas
        else if($this->geoQuotaConfig) {
            foreach($this->geoLocations as $region) {
                $group = "{$region}::";
                $this->registrationsByGroup[$group] = [];
                foreach($this->quotaTypes as $quota) {
                    $quota_group = "{$region}::{$quota}";
                    $this->registrationsByQuotaGroup[$quota_group] = [];
                }
            }
        }
        // 3. faixas ativas
        else if($this->rangesConfig) {
            foreach($this->rangeNames as $range) {
                $group = ":{$range}:";
                $this->registrationsByGroup[$group] = [];
                foreach($this->quotaTypes as $quota) {
                    $quota_group = ":{$range}:{$quota}";
                    $this->registrationsByQuotaGroup[$quota_group] = [];
                }
            }
        }
    }

    /**
     * Retorna os campos utilizados nas cotas
     * 
     * @return array 
     */
    protected function getQuotaFields(): array {
        $fields = ['appliedForQuota'];

        foreach($this->quotaRules as $rule) {
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
        return [];

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
        $fields = array_unique([...$this->quotaFields, ...$this->tiebreakerFields]);

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
        
        $cache_key = false;//"{$this->phase}:quota-registrations:" . md5(serialize($params));
        
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

        $opportunity_proponent_types = $this->firstPhase->registrationProponentTypes;
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

        $meta = $this->geoDivision;
        $region =  $agent_data[$meta] ?? '';

        if(in_array($region, $this->geoLocations)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    function getNumeberOfAvaliableQuotaVacancies(string $group, string $quota_type, int $group_vacancies, int $quota_vacancies, int $group_quota_vacancies): int {
        
        if($this->considerQuotasInGeneralList) {
            $registrations = array_slice($this->registrationsByGroup[$group], 0, $group_vacancies);
        } else {
            $position = $group_vacancies - $group_quota_vacancies;
            $registrations = array_slice($this->registrationsByGroup[$group], $position, $group_quota_vacancies);
        }

        foreach($registrations as $registration) {
            $quota_types = $this->getRegistrationQuotas($registration);
            if(in_array($quota_type, $quota_types)) {
                $quota_vacancies--;
            }
        }

        return max(0,$quota_vacancies);
    }

    // gera resultado das cotas para um grupo
    function generateGroupQuotaResults(string $group, int $group_vacancies): array {
        $group_quota_vacancies = ceil($group_vacancies * $this->quotaVacancies / $this->vacancies);

        $result = array_slice($this->registrationsByGroup[$group], 0, $group_vacancies);

        // gera o número de vagar que devem ser preenchidas para cada tipo de cota
        foreach($this->quotaTypes as $quota) {
            $quota_group = "{$group}{$quota}";
            $quota_vacancies = ceil($group_vacancies * $this->quotaConfig[$quota]->percent);
            $avaliable_quota_vacancies = $this->getNumeberOfAvaliableQuotaVacancies($group, $quota, $group_vacancies, $quota_vacancies, $group_quota_vacancies);

            if($avaliable_quota_vacancies > 0) {
                $avaliable_quota_registrations = array_udiff($this->registrationsByQuotaGroup[$quota_group], $result, fn($reg1, $reg2) => $reg1->id <=> $reg2->id);

                // ordena as inscrições disponíveis para cota por score. as notas mais altas ficam no final do array
                usort($avaliable_quota_registrations, fn($reg1, $reg2) => $reg1->score <=> $reg2->score);
                
                $result = array_reverse($result);
                
                for($i = 0; $i < $avaliable_quota_vacancies; $i++) {
                    // pega a última posição do array para ser a primeira a ser retirada
                    $registration = array_pop($avaliable_quota_registrations);
                    
                    // procura o não cotista com a menor nota e substitui pelo cotista
                    foreach($result as $j => $reg) {
                        
                        if($reg && !$this->getRegistrationQuotas($reg)) {
                            $result[$j] = $registration;
                            break;
                        }
                    }
                }

                $result = array_reverse($result);
            }
        }

        return $result;
    }

    /**
     * Retorna lista de inscrições ordenadas pela classificação final considerando as cotas
     * 
     * @param mixed $params 
     * @return mixed 
     * @throws NotSupported 
     * @throws InvalidArgumentException 
     */
    public function getRegistrationsOrderByScoreConsideringQuotas($params = null): array {
        $app = App::i();

        // HÁ 3 SITUAÇÕES PARA A DISTRIBUIÇÃO DE VAGAS
        // 1. distribuição geográfica e faixas ativas
        // 2. somente distribuição geográfica ativa
        // 3. somente faixas ativas

        // obtendo as inscrições ordenadas pela pontuação considerando critérios de desempate
        $registrations = $this->getRegistrationsForQuotaSorting($params);
        $registrations = $this->tiebreaker($registrations);

        // AGRUPANDO AS INSCRIÇÕES
        foreach($registrations as $registration) {
            $region = $this->getRegistrationRegion($registration);
            $range = $registration->range;

            $group = "{$region}:{$range}:";
            $this->registrationsByGroup[$group][] = $registration;

            if($registration_quotas = $this->getRegistrationQuotas($registration)) {
                foreach($registration_quotas as $registration_quota) {
                    $quota_group = "{$region}:{$range}:{$registration_quota}";
                    $this->registrationsByQuotaGroup[$quota_group][] = $registration;
                }
            }
        }
        
        // DISTRIBUINDO AS VAGAS
        $result = [];
        // 1. distribuição geográfica e faixas ativas
        if($this->geoQuotaConfig && $this->rangesConfig) {
            foreach($this->geoLocations as $region) {
                foreach($this->rangeNames as $range) {
                    $group = "{$region}:{$range}:";
                    $group_vacancies = $this->rangesConfig[$range]->vacancies * $this->geoQuotaConfig[$region]->percent;

                    $result[$group] = $this->generateGroupQuotaResults($group, $group_vacancies);
                }
            }
        } 
        
        // 2. somente distribuição geográfica ativa
        else if($this->geoQuotaConfig) {
            foreach($this->geoLocations as $region) {
                $group = "{$region}::";
                $group_vacancies = $this->geoQuotaConfig[$region]->vacancies;

                $result[$group] = $this->generateGroupQuotaResults($group, $group_vacancies);
            }
        }
         
        // 3. somente faixas ativas
        else if($this->rangesConfig) {
            foreach($this->rangeNames as $range) {
                $group = ":{$range}:";
                $group_vacancies = $this->rangesConfig[$range]->vacancies;

                $result[$group] = $this->generateGroupQuotaResults($group, $group_vacancies);
            }
        }

        // gera uma lista única com as inscrições selecionadas
        $selected_registrations = [];
        foreach($result as $regs) {
            $selected_registrations = array_merge($selected_registrations, $regs);
        }

        // filtra somente resultados válidos
        $selected_registrations = array_filter($selected_registrations);

        // completa a lista de inscrições selecionadas
        $other_registrations = array_udiff($registrations, $selected_registrations, fn($reg1, $reg2) => $reg1->id <=> $reg2->id);
        if(count($selected_registrations) < $this->vacancies) {
            $_registrations = array_slice($other_registrations, 0, $this->vacancies - count($selected_registrations));
            $selected_registrations = array_merge($selected_registrations, $_registrations);
        }

        // ordena pela nota final
        usort($selected_registrations, fn($reg1, $reg2) => $reg2->score <=> $reg1->score);

        // adiciona os demais resultados à lista
        foreach(array_values($other_registrations) as &$reg) {
            if(!in_array($reg, $selected_registrations)){
                $selected_registrations[] = &$reg;
            }
        }

        return $selected_registrations;

    }

    /** 
     * Retorna o slug do tipo de cota
     * 
     * @param object $rule 
     * @return string 
     */
    protected function getQuotaTypeSlugByRule(object $rule): string {
        $app = App::i();
        return isset($rule->title) ? $app->slugify($rule->title) : md5(json_encode($rule));
    }

    /**
     * Retorna os slugs dos tipos de cotas que uma inscrição se enquadrou de acordo com o tipo de proponente da inscrição
     * 
     * @param object $registration 
     * @return array
     */
    protected function getRegistrationQuotas(object $registration): array {
        $result = [];
        if($registration->eligible) {
            $proponent_type = $registration->proponentType ?? 'default';

            foreach($this->quotaRules as $rule) {
                $field_name = $rule->fields->$proponent_type->fieldName;
                if(in_array($registration->$field_name, $rule->fields->$proponent_type->eligibleValues)) {
                    $result[] = $this->getQuotaTypeSlugByRule($rule);
                }
            }
        } 

        return $result;
    }


    public function tiebreaker($registrations) {
        return $registrations;

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