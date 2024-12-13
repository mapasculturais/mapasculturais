<?php

namespace ApiKeywords;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    public function register() {
    }

    public function _init() {
        $app = App::i();

        $format_doc = function ($documento) {
            $documento = preg_replace('#[^\d]*#', '', $documento);
            $formatted = false;
            if (strlen($documento) == 11) {
                $b1 = substr($documento, 0, 3);
                $b2 = substr($documento, 3, 3);
                $b3 = substr($documento, 6, 3);
                $dv = substr($documento, -2);
                $formatted = "$b1.$b2.$b3-$dv";
            } else if (strlen($documento) == 14) {
                $b1 = substr($documento, 0, 2);
                $b2 = substr($documento, 2, 3);
                $b3 = substr($documento, 5, 3);
                $b4 = substr($documento, 8, 4);
                $dv = substr($documento, -2);
                $formatted = "$b1.$b2.$b3/$b4-$dv";
            }

            return $formatted;
        };

        // faz a keyword buscar pelo documento do owner nas inscrições
        $app->hook('repo(Registration).getIdsByKeywordDQL.join', function (&$joins, $keyword, $alias) use ($format_doc) {
            if ($format_doc($keyword)) {
                $joins .= "\n LEFT JOIN o.__metadata doc WITH doc.key IN('documento','cnpj','cpf')";
                $joins .= "\n LEFT JOIN o.__agentRelations coletivo_relation WITH coletivo_relation.group = 'coletivo'";
                $joins .= "\n LEFT JOIN coletivo_relation.agent agent_coletivo";
                $joins .= "\n LEFT JOIN agent_coletivo.__metadata coletivo_doc WITH coletivo_doc.key = 'cnpj'";
            }
        });

        $app->hook('repo(Registration).getIdsByKeywordDQL.where', function (&$where, $keyword, $alias) use ($format_doc) {
            if ($doc = $format_doc($keyword)) {
                $doc2 = trim(str_replace(['%', '.', '/', '-'], '', $keyword));
                $where .= "\n OR doc.value = '$doc' OR doc.value = '$doc2'";
                $where .= "\n OR unaccent(lower(agent_coletivo.name) = unaccent(lower('$keyword'))";
                $where .= "\n OR coletivo_doc.value = '$doc' OR coletivo_doc.value = '$doc2'";
            }
        });

        // faz a keyword buscar pelos termos das taxonomias
        $app->hook('repo(<<agent|space|event|project|opportunity>>).getIdsByKeywordDQL.join', function (&$joins, $keyword, $alias) {
            /** @var \MapasCulturais\Repository $this */
            $taxonomy = App::i()->getRegisteredTaxonomyBySlug('tag');

            $joins .= "LEFT JOIN e.__termRelations tr
                LEFT JOIN
                        tr.term
                            t
                        WITH
                            t.taxonomy = '{$taxonomy->slug}'";
        });

        $app->hook('repo(<<agent|space|event|project|ppportunity>>).getIdsByKeywordDQL.where', function (&$where, $keyword, $alias) {
            $where .= " OR unaccent(lower(t.term)) LIKE unaccent(lower(:{$alias})) ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.join', function (&$joins, $keyword, $alias) {
            $joins .= " LEFT JOIN e.project p
                    LEFT JOIN e.__metadata m
                    WITH
                        m.key = 'subTitle'
                     JOIN e.occurrences oc
                     JOIN oc.space sp
                ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.where', function (&$where, $keyword, $alias) use ($app) {
            $projects = $app->repo('Project')->findByKeyword($keyword);
            $project_ids = [];
            foreach ($projects as $project) {
                $project_ids = array_merge($project_ids, [$project->id], $project->getChildrenIds());
            }
            if ($project_ids) {
                $where .= " OR p.id IN ( " . implode(',', $project_ids) . ")";
            }
            $where .= " OR unaccent(lower(m.value)) LIKE unaccent(lower(:{$alias}))";
            $where .= " OR unaccent(lower(sp.name)) LIKE unaccent(lower(:{$alias}))";
        });
    }
}
