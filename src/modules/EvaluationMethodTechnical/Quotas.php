<?php
namespace EvaluationMethodTechnical;

use Doctrine\ORM\Exception\NotSupported;
use MapasCulturais\API;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
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
 * @property int $quotaVacancies
 * @property array $quotaRules = []
 * @property array $quotaConfig = []
 * @property array $quotaTypes = []
 * @property array $selectedByQuotas = []
 * @property array $rangesConfig = []
 * @property array $rangeNames = []
 * @property string $geoDivision
 * @property object $geoDivisionFields
 * @property array $geoQuotaConfig = []
 * @property array $geoLocations = []
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
    protected float $cutoffScore;
    
    protected int $quotaVacancies;
    protected array $quotaRules = [];
    protected array $quotaConfig = [];
    protected array $quotaTypes = [];
    protected array $selectedByQuotas = [];

    protected array $rangesConfig = [];
    protected array $rangeNames = [];

    protected string $geoDivision;
    protected object $geoDivisionFields;
    protected array $geoQuotaConfig = [];
    protected array $geoLocations = [];

    protected bool $considerQuotasInGeneralList = false;

    /** 
     * Campos utilizados nas cotas, distribuição geográfica e critérios de desempate
     * 
     * @var array
     */
    protected array $registrationFields = [];

    protected static array $instances = [];
        
    function __construct(int $phase_id) {
        $app = App::i();
        
        $this->phase = $app->repo('Opportunity')->find($phase_id);
        $this->firstPhase = $this->phase->firstPhase;
        $this->evaluationConfig = $this->phase->evaluationMethodConfiguration;

        $this->vacancies = $this->firstPhase->vacancies ?: 0;
        $this->cutoffScore = $this->evaluationConfig->cutoffScore ?: 0;

        $this->considerQuotasInGeneralList = $this->firstPhase->considerQuotasInGeneralList;

        // proecessa a configuração de cotas
        $this->quotaRules = $this->evaluationConfig->quotaConfiguration ? ($this->evaluationConfig->quotaConfiguration->rules ?: []) : [];
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
                'percent' => $this->vacancies ? (int) $rule->vacancies / $this->vacancies : 0,
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
                'percent' => $this->vacancies ? (int) $range_vacancies / $this->vacancies : 0
            ];

            $this->rangeNames[] = $range_name;
        }

        // processa a configuração de distribuição geográfica
        if($geo_config = $this->evaluationConfig->geoQuotaConfiguration) {
            $geo_config = (object) $geo_config;
            $distribution = (object) $geo_config->distribution;
            
            $this->geoDivision = $geo_config->geoDivision ?? '';
            $this->geoDivisionFields = (object) ($geo_config->fields ?? []);
            $this->isGeoQuotaActive = (bool) $this->geoDivision;

            $total_geo_vacancies = 0;
            foreach($distribution as $region => $num) {
                if($num) {
                    $this->geoQuotaConfig[$region] = (object) [
                        'vacancies' => $num,
                        'percent' => $this->vacancies ? $num / $this->vacancies : 0
                    ];
                    $this->geoLocations[] = $region;
                    $total_geo_vacancies += $num;
                }
            }
            $this->geoLocations[] = 'OTHERS';
            $this->geoQuotaConfig['OTHERS'] = (object) [
                'vacancies' => $this->vacancies - $total_geo_vacancies,
                'percent' => $this->vacancies ? ($this->vacancies - $total_geo_vacancies) / $this->vacancies : 0
            ];
        }
    }


    /** 
     * Retorna a instância da classe
     * 
     * @param int $phase_id 
     * @return Quotas 
     */
    public static function instance(int $phase_id): Quotas {
        if(!isset(self::$instances[$phase_id])) {
            self::$instances[$phase_id] = new Quotas($phase_id);
        }

        return self::$instances[$phase_id];
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
                if($field && $field->fieldName) {
                    $fields[] = $field->fieldName;
                }
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
    function getRegistrationsForQuotaSorting(): array {
        $app = App::i();
                
        $use_cache = $app->config['app.useQuotasCache'];
        
        $cache_key = false;//"{$this->phase}:quota-registrations:" . md5(serialize($params));
        
        if($use_cache && $app->cache->contains($cache_key)){
            return $app->cache->fetch($cache_key);
        }

        $result = $app->controller('opportunity')->apiFindRegistrations($this->phase, [
            '@select' => implode(',', ['number,range,proponentType,agentsData,consolidatedResult,eligible,score,sentTimestamp', ...$this->fields]),
            '@order' => 'score DESC, id DESC',
            'status' => API::GTE(0)
        ]);

        $registrations = array_map(function ($reg) {
            return (object) $reg; 
        }, $result->registrations);

        if($use_cache){
            $app->cache->save($cache_key, $registrations, $app->config['app.quotasCache.lifetime']);
        }

        foreach($registrations as $registration) {
            $this->getRegistrationQuotas($registration);
            $this->getRegistrationRegion($registration);
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

        $field = isset($this->geoDivisionFields->$registration_proponent_type) ? $this->geoDivisionFields->$registration_proponent_type : null;

        $region = "";
        if($field) {
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
        }

        if(in_array($region, $this->geoLocations)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    public function getRegistrationsOrderByScoreConsideringQuotas(): array {
        $app = App::i();

        // obtendo as inscrições ordenadas pela pontuação considerando critérios de desempate
        $registrations = $this->getRegistrationsForQuotaSorting();
        $registrations = $this->tiebreaker($registrations);


        $range_registrations = [];
        $range_result = [];
        $range_max_registrations = [];

        if($this->isRangeActive) {
            foreach($this->rangeNames as $range) {
                $range_registrations[$range] = [];
                $range_result[$range] = [];
                $range_max_registrations[$range] = $this->rangesConfig[$range]->vacancies;
            }
        } else {
            $range_registrations['default'] = [];
            $range_result['default'] = [];
            $range_max_registrations['default'] = $this->vacancies;
        }

        // agrupa as inscrições por faixa
        foreach($registrations as $registration) {
            if($registration->score < $this->cutoffScore) {
                continue;
            }
            
            $range = $registration->range ?: 'default';
            $range_registrations[$range][] = $registration;
            if(count($range_result[$range]) < $range_max_registrations[$range]) {
                $range_result[$range][] = $registration;
            }
        }

        // tenta garantir as vagas das regiões dentro das faixas
        if($this->isGeoQuotaActive) {
            foreach($range_result as $range => $regs) {
                $geo_count_results = [];
                $geo_vacancies = [];
                foreach($regs as $registration) {
                    $region = $this->getRegistrationRegion($registration);
                    $geo_count_results[$region] = $geo_count_results[$region] ?? 0;
                    $geo_count_results[$region]++;
                }
                $range_vacancies = $range_max_registrations[$range];
                foreach($this->geoLocations as $region) {
                    $geo_vacancies[$region] = ceil($range_vacancies * $this->geoQuotaConfig[$region]->percent);
                }

                foreach($this->geoLocations as $region) {
                    foreach($range_registrations[$range] as $registration){
                        $registration_region = $this->getRegistrationRegion($registration);

                        $_count_result = $geo_count_results[$region] ?? 0;
                        $_vacancies = $geo_vacancies[$region] ?? 0;

                        /*
                         O número de inscrições da região é maior do que o número de vagas da região, então 
                         precisa substituir uma inscrição da região ($region) por uma inscrição de outra região com vagas sobrando
                         */
                        if($_count_result > $_vacancies) {

                            // obtém a região que ainda tem vagas
                            $regions_with_vacancies = [];
                            foreach($this->geoLocations as $_region) {
                                if($_region != $region && ($geo_count_results[$_region] ?? 0) < ($geo_vacancies[$_region] ?? 0)) {
                                    $regions_with_vacancies[] = $_region;
                                }
                            }

                            // obtém a posição da última inscrição da região atual ($region)
                            $key_of_registration_to_exclude = null;
                            foreach($range_result[$range] as $key => $_registration) {
                                $_registration_region = $this->getRegistrationRegion($_registration);
                                if($_registration_region == $region) {
                                    $key_of_registration_to_exclude = $key;
                                }
                            }


                            if(!$this->isRegistrationInArray($registration, $range_result[$range]) && in_array($registration_region, $regions_with_vacancies)) {
                                $geo_count_results[$registration_region]++;
                                $geo_count_results[$region]--;
                                $range_result[$range][$key_of_registration_to_exclude] = $registration;
                            }
                        }
                    }
                }
            }
        }

        // aplica as cotas nas faixas
        if($this->isQuotaActive) {
            foreach($range_result as $range => $regs) {
                $_result = &$range_result[$range];
                $_registrations = $range_registrations[$range];
                /*
                    Se o número de inscrições "selecionadas" na faixa não for menor que o 
                    número de inscrições totais na faixa, não há quem colocar como cotista,
                    então pula para a próxima faixa
                */
                if(!(count($_result) < count($_registrations))) {
                    continue;
                }

                $range_vacancies = isset($this->rangesConfig[$range]) && isset($this->rangesConfig[$range]->vacancies) ? (int) $this->rangesConfig[$range]->vacancies : 0;
                $range_quota_vacancies = [];
                $range_total_quota_vacancies = 0;
                foreach($this->quotaConfig as $quota_slug => $quota_config) {
                    $quota_percent = $quota_config->percent;
                    $quota_vacancies = ceil($range_vacancies * $quota_percent);

                    /* 
                        O número de vagas de cotas dentro das faixas será arredondado para cima,
                        então se houver, por exemplo, 5% de vagas numa faixa com 25 vagas, 
                        o sistema considerará 2 vagas para cotistas 
                    */
                    $range_quota_vacancies[$quota_slug] = $quota_vacancies;
                    $range_total_quota_vacancies += $quota_vacancies;
                }

                /*
                    Caso a oportunidade esteja configurada para considerar os cotistas dentro da 
                    ampla concorrência, começa a contar do início da lista, caso contrário começa a 
                    contar a partir da posição das vagas exclusivas para cotistas
                    por exemplo: se há 10 vagas e 2 vagas para cotistas, verifica se os 2 últimos 
                    posicionados são cotistas e contabiliza esses como cotistas.
                */
                
                foreach($this->quotaConfig as $quota_slug => $quota_config) {
                    $first_quota_index = $this->considerQuotasInGeneralList ? 
                        0 : count($_result) - $range_total_quota_vacancies;
    
                    $avaliable_quota_vacancies = $range_quota_vacancies[$quota_slug];

                    // calcula o número de vagas ainda disponíveis para o tipo de cota
                    for($i = count($_result) -1; $i >= $first_quota_index; $i--) {
                        // se não tem mais vagas para este tipo de cota
                        if($avaliable_quota_vacancies <= 0) {
                            break;
                        }
                        $registration = $_result[$i];
                        
                        if($this->isRegistrationEligibleForQuota($registration, $quota_slug)) {
                            $avaliable_quota_vacancies--;
                            $this->setRegistrationAsQuota($registration, $quota_slug);

                        }
                    }
                    /*
                        Preenche as vagas para o tipo de cota, procurando na lista total de inscrições da faixa
                        por inscrições que não estejam na lista de classificados da faixa e que se enquadrem como cotista;
                        e substituindo a inscrição com menor valor que não se enquadre em nenhum tipo de cota.
                        Caso a oportunidade use divisão geográfica, tenta substituir a inscrição da mesma região geográfica
                    */
                    foreach($_registrations as $registration) {
                        // se não tem mais vagas para este tipo de cota
                        if($avaliable_quota_vacancies <= 0) {
                            break;
                        } 

                        // encontra o primeiro cotista
                        if(!$this->isRegistrationInArray($registration, $_result) && $this->isRegistrationEligibleForQuota($registration, $quota_slug)) {
                            // substitui o não cotista com nota mais baixa pelo cotista encontrado
                            $region = $this->getRegistrationRegion($registration);
                            $replaced = false;

                            // primeiro tenta substituir dentro da mesma região
                            for($i = count($_result) - 1; $i >= 0; $i--) {
                                if(!$this->getRegistrationQuotas($_result[$i])) {
                                    $_region = $this->getRegistrationRegion($_result[$i]);
                                    if($_region == $region) {
                                        $this->setRegistrationAsQuota($registration, $quota_slug, $_result[$i]);
                                        $_result[$i] = $registration;
                                        $replaced = true;
                                        break;
                                    }
                                }
                            }

                            // se não conseguiu substituir dentro da mesma região, desconsidera a região.
                            if(!$replaced) {
                                for($i = count($_result) -1; $i >= 0; $i--) {
                                    if(!$this->getRegistrationQuotas($_result[$i])) {
                                        $this->setRegistrationAsQuota($registration, $quota_slug, $_result[$i]);
                                        $_result[$i] = $registration;
                                        $replaced = true;
                                        break;
                                    }
                                }
                            }

                            if($replaced) {
                                $avaliable_quota_vacancies--;
                            }
                        }
                    }
                }
            }
        }

        $result = [];
        foreach($range_result as $regs) {
            foreach($regs as $reg) {
                $result[] = $reg;
            }
        }
        
        $result = $this->tiebreaker($result);

        foreach($registrations as $registration) {            
            if(!$this->isRegistrationInArray($registration, $result)) {
                $result[] = $registration;
            }
        }
        
        return $result;
    }

    public function isRegistrationInArray($registration, array $list_of_registrations): bool {
        foreach($list_of_registrations as $reg) {
            if($reg->number == $registration->number) {
                return true;
            }
        }

        return false;
    }

    public function setRegistrationAsQuota(object $registration, string $quota_slug, object $replaced_registration = null) {
        $registration->usingQuota = true;
        if ($replaced_registration) {
            $this->registrationFields[$registration->id]['usingQuota'] = $quota_slug . "\n" . sprintf(i::__("(substituindo %s)"), $replaced_registration->number);
        } else {
            $this->registrationFields[$registration->id]['usingQuota'] = $quota_slug;
        }
    }

    public function isRegistrationEligibleForQuota(object $registration, string $quota_slug): bool {
        $registration_quotas = $this->getRegistrationQuotas($registration);
        return in_array($quota_slug, $registration_quotas);
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
                $field_name = $rule->fields->$proponent_type->fieldName ??  null;
                if($field_name && in_array($registration->$field_name, $rule->fields->$proponent_type->eligibleValues)) {
                    $result[] = $this->getQuotaTypeSlugByRule($rule);
                    $quotas[] = $rule->title;
                }
            }
        } 
        
        $this->registrationFields[$registration->id] = $this->registrationFields[$registration->id] ?? [];
        $this->registrationFields[$registration->id]['quotas'] = $quotas;
        $this->registrationFields[$registration->id]['appliedForQuota'] = $registration->appliedForQuota;
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
    
        } else if ($tiebreaker->criterionType == 'submissionDate') {
            $key = $tiebreaker->criterionType;
            $value = $registration->sentTimestamp;
    
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
            
            if($tiebreaker->criterionType == 'submissionDate') {
                $must_fetch_evaluation_data = true;
                break;
            }
        }
        
        $evaluation_data = [];
        if($must_fetch_evaluation_data) {
            $evaluation_data = $this->fetchEvaluationData($registrations);
        }

        usort($registrations, function($registration1, $registration2) use($tiebreaker_configuration, $evaluation_data) {
            $result = $registration2->score <=> $registration1->score;
            if($result != 0) {
                return $result;
            }

            foreach($tiebreaker_configuration as $tiebreaker) {
                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'submissionDate') {
                    $registration1Has = property_exists($registration1, 'sentTimestamp') ? $registration1->sentTimestamp : null;
                    $registration2Has = property_exists($registration2, 'sentTimestamp') ? $registration2->sentTimestamp : null;
                
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
