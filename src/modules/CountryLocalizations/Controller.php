<?php

namespace CountryLocalizations;

use MapasCulturais\App;

class Controller extends \MapasCulturais\Controller
{   
    function API_findSublevels(){
        $app = App::i();
        $default_country_code = $app->config['address.defaultCountryCode'];
        $localization = $app->getRegisteredCountryLocalizationByCountryCode($default_country_code);
        $hierarchy = $localization->levelHierarchy;

        $requested_level = (int) ($this->data['level'] ?? null);
        $filters = array_diff_key($this->data, ['level' => '']) ?? [];

        if (!$requested_level) {
            $this->errorJson(true);
        }

        foreach ($filters as $key => $value) {
            if (preg_match('/^level:\d+$/', $key) && is_string($value) && strpos($value, ',') !== false) {
                $filters[$key] = explode(',', $value);
            }
        }

        $trail = [];
        $level = 0;

        // Se o nível 0 é um país (como 'Brasil')
        if (isset($hierarchy[0]) && is_string($hierarchy[0])) {
            $trail["level:$level"] = ['code' => $hierarchy[0], 'label' => $hierarchy[0]];
            $hierarchy = $hierarchy[1];
            $level++;
        }

        // pega só as keys do filtro que são do tipo "level:X"
        $filter_levels = array_filter(array_keys($filters), function($k) {
            return preg_match('/^level:\d+$/', $k);
        });

        usort($filter_levels, function($a, $b) {
            return (int) substr($a, 6) <=> (int) substr($b, 6);
        });

        $results = [];

        // Iiteração sobre filtros múltiplos
        $recursive_find = function($current_hierarchy, $current_level, $current_trail, $filter_levels, $filters, $requested_level) use (&$recursive_find, &$results) {
            // Se não há mais níveis de filtro para processar, chama findSublevels normalmente
            if (empty($filter_levels)) {
                $partial = $this->findSublevels($current_hierarchy, $current_level, $current_trail, $requested_level, $filters);
                $results = array_merge($results, $partial);
                return;
            }

            // Pega o próximo nível para filtrar
            $level_key = array_shift($filter_levels);
            $codes = (array) ($filters[$level_key] ?? []);

            if (empty($codes)) {
                $recursive_find($current_hierarchy, $current_level, $current_trail, $filter_levels, $filters, $requested_level);
                return;
            }

            // Para cada código nesse nível, tenta achar no hierarchy, monta o trail e continua
            foreach ($codes as $code) {
                $found = false;
                foreach ($current_hierarchy as $key => $val) {
                    $label = is_array($val) ? ($val[0] ?? $key) : $val;
                    $node_code = is_string($key) ? $key : $label;

                    if ($node_code === $code) {
                        $new_trail = $current_trail;
                        $new_trail[$level_key] = ['code' => $node_code, 'label' => $label];
                        $new_hierarchy = (is_array($val) && isset($val[1])) ? $val[1] : [];

                        $recursive_find($new_hierarchy, (int)substr($level_key, 6) + 1, $new_trail, $filter_levels, $filters, $requested_level);
                        $found = true;
                        break;
                    }
                }
            }
        };

        // Chama função recursiva com todos os filtros de nível
        $recursive_find($hierarchy, $level, $trail, $filter_levels, $filters, $requested_level);

        $this->json($results);
    }

    /**
     * Busca os subníveis da hierarquia a partir do nível atual, respeitando filtros e construindo
     * o resultado com informações de nível, código, label e referência à trilha.
     *
     * @param array       $hierarchy      Hierarquia atual (array) onde cada chave/valor representa um nível ou subnível.
     * @param int         $current_level  Nível atual na hierarquia sendo processado.
     * @param array       $trail          Trilha dos níveis já percorridos, com código e label.
     * @param int         $target_level   Nível alvo para o qual queremos obter os subníveis.
     * @param array       $filters        Filtros aplicados para restringir os níveis retornados.
     *
     * @return array Retorna um array de subníveis que inclui 'level', 'label', 'code' e a referência ao último filtro válido na trilha.
    */
    function findSublevels($hierarchy, $current_level, $trail, $target_level, $filters) {
        $result = [];

        if (!is_array($hierarchy)) {
            return $result;
        }

        foreach ($hierarchy as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_string($value[0]) && !isset($value[1])) {
                $label = $value[0];
                $code = $label;

                $filter_level = $this->getLastFilterInTrail($filters, $trail);

                $result[] = [
                    'level' => $target_level,
                    'label' => $label,
                    'code' => $code,
                    $filter_level => $trail[$filter_level] ?? null
                ];
                continue;
            }

            $label = is_array($value) ? ($value[0] ?? $key) : $value;
            $code = is_string($key) ? $key : $label;

            $trail["level:$current_level"] = [
                'code' => $code,
                'label' => $label
            ];

            if (isset($filters["level:$current_level"])) {
                $allowed = (array) $filters["level:$current_level"];
                if (!in_array($code, $allowed)) {
                    continue;
                }
            }

            if ($current_level == $target_level) {
                $filter_level = $this->getLastFilterInTrail($filters, $trail);

                $result[] = [
                    'level' => $target_level,
                    'label' => $label,
                    'code' => $code,
                    $filter_level => $trail[$filter_level] ?? null
                ];
            }

            if (is_array($value) && isset($value[1])) {
                $children = $this->findSublevels($value[1], $current_level + 1, $trail, $target_level, $filters);
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    /**
     * Retorna o último nível de filtro presente na trilha (`trail`) com base nos filtros aplicados.
     *
     * Percorre os filtros do tipo "level:X" ordenados e verifica qual desses níveis está presente na trilha,
     * retornando o último nível encontrado. Se nenhum for encontrado, retorna 'level:0' como padrão.
     *
     * @param array $filters Array de filtros onde as chaves são do tipo "level:X".
     * @param array $trail Trilha atual que contém os níveis com seus códigos e labels.
     *
     * @return string O último nível de filtro encontrado na trilha (ex: "level:2").
    */
    function getLastFilterInTrail($filters, $trail) {
        $filtered_levels = array_filter(array_keys($filters), function($key) {
            return preg_match('/^level:\d+$/', $key);
        });

        sort($filtered_levels);

        $last_level = 'level:0';
        foreach ($filtered_levels as $level_key) {
            if (isset($trail[$level_key])) {
                $last_level = $level_key;
            }
        }

        return $last_level;
    }
}
