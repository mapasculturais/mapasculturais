<?php

namespace Tests\Doubles;

use EvaluationMethodTechnical\Quotas;
use MapasCulturais\App;

/**
 * Referência da classificação por cotas anterior à otimização de desempenho.
 *
 * Guarda cópias literais dos métodos de Quotas como eram antes de serem
 * otimizados, para que o teste de caracterização compare as duas versões sobre
 * os mesmos dados e garanta que a otimização não muda o resultado.
 */
class LegacyQuotas extends Quotas
{
    use InjectedQuotaData;

    protected function getTiebreakerSelected(object $tiebreaker): ?object {
        if (isset($tiebreaker->selected)) {
            return $tiebreaker->selected;
        }

        $criterion_type = $tiebreaker->criterionType ?? null;
        if (!$criterion_type || !str_starts_with($criterion_type, 'field_')) {
            return null;
        }

        foreach ($this->firstPhase->registrationFieldConfigurations as $field) {
            if ($field->fieldName === $criterion_type) {
                $field_type = $field->fieldType;

                // agent-owner/collective fields store the entity field type (select, date, …)
                // in Agent metadata; without resolving it, tiebreaker comparisons are skipped.
                if (in_array($field_type, ['agent-owner-field', 'agent-collective-field'], true)) {
                    $entity_field = $field->config['entityField'] ?? null;
                    $agent_meta = \MapasCulturais\Entities\Agent::getPropertiesMetadata();
                    if ($entity_field && isset($agent_meta[$entity_field]['type'])) {
                        $field_type = $agent_meta[$entity_field]['type'];
                    }
                }

                return (object) [
                    'title' => $field->title,
                    'fieldType' => $field_type,
                ];
            }
        }

        return null;
    }

    /**
     * Preenche campos de agente (ex.: pessoaDeficiente, raca) a partir de agentsData quando não estão no meta.
     */
    protected function applyAgentOwnerFieldsFromAgentsData(object $registration): void {
        $agents_data = $registration->agentsData ?? [];
        if (!$agents_data) {
            return;
        }

        $app = App::i();
        $proponent_types_map = $app->config['registration.proponentTypesToAgentsMap'] ?? [];
        $proponent_type = $registration->proponentType ?? 'default';
        $agent_key = $proponent_types_map[$proponent_type] ?? 'owner';
        $agent_data = $agents_data[$agent_key] ?? $agents_data['owner'] ?? null;

        if (!$agent_data) {
            return;
        }

        foreach ($this->firstPhase->registrationFieldConfigurations as $field) {
            $field_name = $field->fieldName;
            if (!in_array($field_name, $this->fields, true)) {
                continue;
            }
            if (!in_array($field->fieldType, ['agent-owner-field', 'agent-collective-field'], true)) {
                continue;
            }

            $entity_field = $field->config['entityField'] ?? null;
            if (!$entity_field || !isset($agent_data[$entity_field])) {
                continue;
            }

            if ($this->isQuotaFieldValueEmpty($registration->$field_name ?? null)) {
                $registration->$field_name = $agent_data[$entity_field];
            }
        }
    }


    public function getRegistrationsOrderByScoreConsideringQuotas(): array {
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
            
            if (!isset($range_registrations[$range])) {
                $range_registrations[$range] = [];
            }
            if (!isset($range_result[$range])) {
                $range_result[$range] = [];
            }
            if (!isset($range_max_registrations[$range])) {
                $range_max_registrations[$range] = 0;
            }
            
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
                                $geo_count_results[$registration_region] = ($geo_count_results[$registration_region] ?? 0) + 1;
                                $geo_count_results[$region] = ($geo_count_results[$region] ?? 0) - 1;
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
                        if(!isset($_result[$i]) || $avaliable_quota_vacancies <= 0) {
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


    /**
     * Retorna os campos utilizados
     * @return array 
     */
    protected function getFields(): array {
        $fields = array_unique([...$this->quotaFields, ...$this->tiebreakerFields, ...$this->geoQuotaFields]);

        return $fields;
    }
}
