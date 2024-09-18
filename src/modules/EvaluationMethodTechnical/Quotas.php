<?php
namespace EvaluationMethodTechnical;

use Doctrine\ORM\Exception\NotSupported;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationEvaluation;
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
 * 
 * @property Opportunity $firstPhase
 * @property Opportunity $phase
 * @property EvaluationMethodConfiguration $evaluationConfig
 * @property array $tiebreakerConfig
 * @property int $vacancies
 * @property array $selectedGlobal = []
 * @property int $quotaVacancies
 * @property array $quotaRules = []
 * @property array $quotaConfig = []
 * @property array $quotaTypes = []
 * @property array $selectedByQuotas = []
 * @property array $rangesConfig = []
 * @property array $rangeNames = []
 * @property array $selectedByRanges = []
 * @property string $geoDivision
 * @property object $geoDivisionFields
 * @property array $geoQuotaConfig = []
 * @property array $geoLocations = []
 * @property array $selectedByGeo = []
 * @property array $registrationsByGroup = []
 * @property array $registrationsByQuotaGroup = []
 * @property bool $considerQuotasInGeneralList = false
 * @property array $registrationFields = []
 * 
 * @package EvaluationMethodTechnical
 */
class Quotas {
    use Traits\MagicGetter,
        Traits\MagicSetter;

    protected Opportunity $firstPhase;
    protected Opportunity $phase;
    protected EvaluationMethodConfiguration $evaluationConfig;

    protected bool $isQuotaActive = false;
    protected bool $isGeoQuotaActive = false;
    protected bool $isRangeActive = false;

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

    /** 
     * Campos utilizados nas cotas, distribuição geográfica e critérios de desempate
     * 
     * @var array
     */
    protected array $registrationFields = [];
        
    function __construct(int $phase_id) {
        $app = App::i();
        
        $this->phase = $app->repo('Opportunity')->find($phase_id);
        $this->firstPhase = $this->phase->firstPhase;
        $this->evaluationConfig = $this->phase->evaluationMethodConfiguration;

        $this->vacancies = $this->firstPhase->vacancies;

        $this->considerQuotasInGeneralList = $this->firstPhase->considerQuotasInGeneralList;

        // proecessa a configuração de cotas
        $this->quotaRules = $this->evaluationConfig->quotaConfiguration->rules ?: [];
        $this->tiebreakerConfig = array_values((array) $this->evaluationConfig->tiebreakerCriteriaConfiguration ?: []);
        
        $this->quotaVacancies = 0;
        $this->isQuotaActive = (bool) $this->quotaRules;
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
        $this->isRangeActive = (bool) $registration_ranges;
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
            $this->geoDivisionFields = (object) $geo_config->fields;
            $this->isGeoQuotaActive = (bool) $this->geoDivision;

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
     * Retorna a lista de campos necessários para a distribuição geográfica
     * @return array 
     */
    protected function getGeoQuotaFields(): array {
        $fields = [];
        foreach($this->geoDivisionFields as $field) {
            if($field != 'geo') {
                $fields[] = $field;
            }
        }

        return $fields;
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
        $fields = array_unique([...$this->quotaFields, ...$this->tiebreakerFields, ...$this->geoQuotaFields]);

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

        $registration_proponent_type = $registration->proponentType ?: 'default';

        $field = $this->geoDivisionFields->$registration_proponent_type;

        if($field == 'geo') {
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
        } else {
            $region = $registration->$field;
        }

        $this->registrationFields[$registration->id] = $this->registrationFields[$registration->id] ?? [];
        $this->registrationFields[$registration->id]['region'] = $region;


        if(in_array($region, $this->geoLocations)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    /**
     * Retorna o número de vagas disponíveis para um grupo
     * 
     * @param string $group 
     * @param int $group_vacancies 
     * @return int 
     */
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

    /**
     * Gera o resultado das cotas para um grupo
     * 
     * @param string $group 
     * @param int $group_vacancies 
     * @return array 
     */
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
            
            $region = $this->isGeoQuotaActive ? $this->getRegistrationRegion($registration) : '';
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
        $selected_registrations = $this->tiebreaker($selected_registrations);

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
        $quotas = [];
        if($registration->eligible) {
            $proponent_type = $registration->proponentType ?? 'default';

            foreach($this->quotaRules as $rule) {
                $field_name = $rule->fields->$proponent_type->fieldName;
                if(in_array($registration->$field_name, $rule->fields->$proponent_type->eligibleValues)) {
                    $result[] = $this->getQuotaTypeSlugByRule($rule);
                    $quotas[] = $rule->title;
                }
            }
        } 
        
        $this->registrationFields[$registration->id] = $this->registrationFields[$registration->id] ?? [];
        $this->registrationFields[$registration->id]['quotas'] = $quotas;

        return $result;
    }

    private function getCriterionName(string $criterion_id): string {
        foreach($this->evaluationConfig->criteria as $criterion) {
            if($criterion->id == $criterion_id) {
                return $criterion->title;
            }
        }

        return '';
    }

    /**
     * Retorna o nome da seção de critério de avaliação dado o id
     * @param string $sectionCriteria 
     * @return string 
     */
    private function getSectionCriterionName(string $section_id): string {
        foreach($this->evaluationConfig->sections as $section) {
            if($section->id == $section_id) {
                return $section->name;
            }
        }

        return '';
    }

    private function saveRegistrationTiebreaker($registration, $tiebreaker, $value = null) {
        $this->registrationFields[$registration->id] = $this->registrationFields[$registration->id] ?? [];
        $this->registrationFields[$registration->id]['tiebreaker'] = $this->registrationFields[$registration->id]['tiebreaker'] ?? [];
    
        if ($tiebreaker->criterionType == 'criterion') {
            $key = $this->getCriterionName($tiebreaker->preferences);
        } else if ($tiebreaker->criterionType == 'sectionCriteria') {
            $key = $this->getSectionCriterionName($tiebreaker->preferences);
    
        } else {
            $key = $tiebreaker->selected->title;
            
            if (property_exists($registration, $tiebreaker->criterionType)) {
                $value = $registration->{$tiebreaker->criterionType};
            } else {
                $value = null;
            }
        }
        
        $this->registrationFields[$registration->id]['tiebreaker'][$key] = $value;
    }

    /**
     * Retorna a lista de inscrições ordenadas de acordo com os critérios de desempate
     * 
     * @param array $registrations 
     * @return array 
     */
    public function tiebreaker($registrations) {
        $tiebreaker_configuration = $this->tiebreakerConfig;

        $must_fetch_evaluation_data = false;
        foreach($tiebreaker_configuration as $tiebreaker) {
            if($tiebreaker->criterionType == 'criterion') {
                $must_fetch_evaluation_data = true;
                break;
            }

            if($tiebreaker->criterionType == 'sectionCriteria') {
                $must_fetch_evaluation_data = true;
                break;
            }
        }

        if($must_fetch_evaluation_data) {
            $evaluation_data = $this->fetchEvaluationData($registrations);
        }

        usort($registrations, function($registration1, $registration2) use($tiebreaker_configuration, $evaluation_data) {
            $result = $registration2->score <=> $registration1->score;
            if($result != 0) {
                return $result;
            }

            foreach($tiebreaker_configuration as $tiebreaker) {
                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'criterion') {
                    $registration1Has = $this->tiebreakerCriterion($tiebreaker->preferences, $registration1->id, $evaluation_data);
                    $registration2Has = $this->tiebreakerCriterion($tiebreaker->preferences, $registration2->id, $evaluation_data);
                    
                    $this->saveRegistrationTiebreaker($registration1, $tiebreaker, $registration1Has);
                    $this->saveRegistrationTiebreaker($registration2, $tiebreaker, $registration2Has);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }
                
                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'sectionCriteria') {
                    $registration1Has = $this->tiebreakerSectionCriteria($tiebreaker->preferences, $registration1->id, $evaluation_data);
                    $registration2Has = $this->tiebreakerSectionCriteria($tiebreaker->preferences, $registration2->id, $evaluation_data);

                    $this->saveRegistrationTiebreaker($registration1, $tiebreaker, $registration1Has);
                    $this->saveRegistrationTiebreaker($registration2, $tiebreaker, $registration2Has);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                $selected = $tiebreaker->selected;
                if(is_null($selected)) {
                    continue;
                }

                if($selected->fieldType == 'select') {
                    $registration1Has = in_array($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = in_array($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    
                    if($registration1Has) {
                        $this->saveRegistrationTiebreaker($registration1, $tiebreaker);
                    }
                    if($registration2Has) {
                        $this->saveRegistrationTiebreaker($registration2, $tiebreaker);
                    }

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                if (in_array($selected->fieldType, ['integer', 'numeric', 'number', 'float', 'currency', 'date'])) {
                    $registration1Has = property_exists($registration1, $tiebreaker->criterionType) ? $registration1->{$tiebreaker->criterionType} : null;
                    $registration2Has = property_exists($registration2, $tiebreaker->criterionType) ? $registration2->{$tiebreaker->criterionType} : null;
                
                    $this->saveRegistrationTiebreaker($registration1, $tiebreaker);
                    $this->saveRegistrationTiebreaker($registration2, $tiebreaker);
                
                    if ($registration1Has !== null && $registration2Has !== null) {
                        $result = $registration1Has <=> $registration2Has;
                
                        if ($tiebreaker->preferences == 'smallest') {
                            if ($result !== 0) {
                                return $result;
                            }
                        }
                
                        if ($tiebreaker->preferences == 'largest') {
                            if ($result !== 0) {
                                return -$result;
                            }
                        }
                    }
                }
                

                if(in_array($selected->fieldType, ['multiselect', 'checkboxes'])) {
                    $registration1Has = array_intersect($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = array_intersect($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);

                    $registration1Has = !empty($registration1Has);
                    $registration2Has = !empty($registration2Has);

                    if($registration1Has) {
                        $this->saveRegistrationTiebreaker($registration1, $tiebreaker);
                    }
                    if($registration2Has) {
                        $this->saveRegistrationTiebreaker($registration2, $tiebreaker);
                    }

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }
                
                if(in_array($selected->fieldType, ['boolean', 'checkbox'])) {
                    $registration1Has = $registration1->{$tiebreaker->criterionType};
                    $registration2Has = $registration2->{$tiebreaker->criterionType};

                    $this->saveRegistrationTiebreaker($registration1, $tiebreaker);
                    $this->saveRegistrationTiebreaker($registration2, $tiebreaker);

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
            }

        });
        return $registrations;
    }

    /**
     * Retorna os dados de avaliação das inscrições
     * 
     * @param array $registrations 
     * @return array 
     */
    public function fetchEvaluationData(array $registrations): array {
        $app = App::i();
        
        $sql = "SELECT registration_id, evaluation_data FROM registration_evaluation WHERE registration_id IN (:registrations) AND status IN (:status)";

        $status = [ 
            RegistrationEvaluation::STATUS_EVALUATED,
            RegistrationEvaluation::STATUS_SENT
        ];

        $registrations_ids = array_map(fn($reg) => $reg->id, $registrations);

        $evaluations_data = $app->em->getConnection()->executeQuery($sql, [
            'registrations' => $registrations_ids,
            'status' => $status
        ], [
            'registrations' => \Doctrine\DBAL\ArrayParameterType::INTEGER,
            'status' => \Doctrine\DBAL\ArrayParameterType::STRING
        ])->fetchAll();

        $grouped = [];

        foreach($evaluations_data as $data) {
            $evaluation = json_decode($data['evaluation_data']);
            $registration_id = $data['registration_id'];

            $grouped[$registration_id] = $grouped[$registration_id] ?? [];
            $grouped[$registration_id][] = $evaluation;
        }

        $result = [];

        foreach($grouped as $registration_id => $evaluations) {
            $result[$registration_id] = [];
            $keys_count = [];
            foreach($evaluations as $evaluation) {
                foreach($evaluation as $key => $value) {
                    if(!str_starts_with($key, 'c-')) {
                        continue;
                    }
                    $keys_count[$key] = $keys_count[$key] ?? 0;
                    $keys_count[$key]++;

                    $result[$registration_id][$key] = $result[$registration_id][$key] ?? 0;
                    $result[$registration_id][$key] += $value;
                }
            }

            foreach($result[$registration_id] as $key => $value) {
                $result[$registration_id][$key] = $value / $keys_count[$key];
            }
        }

        return $result;
    }
    
    /**
     * Retorna a média de um critério de avaliação para uma inscrição
     * 
     * @param mixed $criteria_id 
     * @param mixed $registration_id 
     * @return float 
     */
    public function tiebreakerCriterion($criteria_id, $registration_id, $evaluation_data) {
        $criteria = [];
        foreach($this->evaluationConfig->criteria as $criterion) {
            if($criterion->id === $criteria_id) {
                $criteria[] = $criterion;
            }
        }

        $evaluation_data = $evaluation_data[$registration_id] ?? [];

        $result = 0;
        foreach($evaluation_data as $key => $data) {
            foreach($criteria as $cri) {
                if($key === $cri->id) {
                    $result = $data * $cri->weight;
                }
            }
        }
        
        return number_format($result, 2);
    }

    /** 
     * Retorna a média de uma seção de critérios de avaliação para uma inscrição
     * 
     * @param mixed $section_id 
     * @param mixed $registration_id 
     * @return float 
     */
    public function tiebreakerSectionCriteria($section_id, $registration_id, array $evaluation_data) {
        $criteria = [];
        foreach($this->evaluationConfig->criteria as $criterion) {
            if($criterion->sid === $section_id) {
                $criteria[] = $criterion;
            }
        }

        $evaluation_data = $evaluation_data[$registration_id] ?? [];

        $result = 0;
        foreach($evaluation_data as $key => $data) {
            foreach($criteria as $cri) {
                if($key === $cri->id) {
                    $result += $data * $cri->weight;
                }
            }
        }

        return number_format($result, 2);
    }
}
