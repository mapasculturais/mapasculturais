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

        // ordena por nível numérico crescente
        usort($filter_levels, function($a, $b) {
            return (int) substr($a, 6) <=> (int) substr($b, 6);
        });

        // percorre os filtros na ordem correta
        foreach ($filter_levels as $level_key) {
            $code = $filters[$level_key];
            $found = false;

            foreach ($hierarchy as $key => $val) {
                $label = is_array($val) ? ($val[0] ?? $key) : $val;
                $node_code = is_string($key) ? $key : $label;

                if ($node_code === $code) {
                    $trail[$level_key] = ['code' => $node_code, 'label' => $label];
                    $hierarchy = is_array($val) && isset($val[1]) ? $val[1] : [];
                    $found = true;
                    break;
                }
            }

            if (!$found) break;
        }

        $result = $this->findSublevels($hierarchy, $level, $trail, $requested_level, $filters);
        $this->json($result);
    }

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
