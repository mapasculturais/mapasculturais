<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;
use SealExemption\SealExemptionService;

/**
 * @property-read string $fileGroup
 * @package Spreadsheets
 */
abstract class EvaluationsSpreadsheetJob extends SpreadsheetJob
{
    function _getHeader(Job $job): array
    {
        // Parte comum a todos os métodos de avaliação
        $entity_class_name = $job->entityClassName;     
        $registration_class_name = Registration::class;

        $query = $job->query;
        $properties = explode(',', $query['@select']);
        
        $evaluator_fields = ['committeeSequentialNumber', 'valuerUserId', 'valuerAgentId', 'user'];
        $evaluator_col_start = null;
        $evaluator_col_end = null;

        $header = [];
        $sub_header = [];
        $total_properties = 0;
        $job->owner->registerRegistrationMetadata(true);
        foreach($properties as $property) {
            if (!in_array($property, ['result', 'status', 'evaluationData'])) {
                if($this->slug !== 'continuous-spreadsheets' && $property === 'goalStatuses') {
                    continue;
                }

                if (in_array($property, $evaluator_fields, true)) {
                    if ($evaluator_col_start === null) {
                        $evaluator_col_start = $total_properties + 1;
                    }
                    $evaluator_col_end = $total_properties + 1;
                }

                $total_properties++;

                if($property === 'projectName') {
                    $sub_header[$property] = i::__('Nome do projeto');
                    continue;
                }

                if(str_starts_with($property, 'owner.{')) {
                    $values = $this->extractValues($property);

                    foreach($values as $val) {
                        if($val === 'name') {
                            $sub_header[$val] = i::__('Agente responsável');
                        }
                    }
                    continue;
                }

                if($property === 'committeeSequentialNumber') {
                    $sub_header[$property] = i::__('Nº sequencial do avaliador');
                    continue;
                }

                if($property === 'valuerUserId') {
                    $sub_header[$property] = i::__('ID do usuário do avaliador');
                    continue;
                }

                if($property === 'valuerAgentId') {
                    $sub_header[$property] = i::__('ID do agente avaliador');
                    continue;
                }
                
                if($property === 'user') {
                    $sub_header[$property] = i::__('Nome do avaliador');
                    continue;
                }

                $sub_header[$property] = $registration_class_name::getPropertyLabel($property) ?: $property;
            }
        }

        $registration_col_end = ($evaluator_col_start ?? $total_properties + 1) - 1;
        $column_registration_info = $registration_col_end >= 1
            ? "A1:{$this->getSpreadsheetColumnName($registration_col_end)}1"
            : 'A1:A1';

        $column_evaluator_info = ($evaluator_col_start && $evaluator_col_end)
            ? "{$this->getSpreadsheetColumnName($evaluator_col_start)}1:{$this->getSpreadsheetColumnName($evaluator_col_end)}1"
            : "{$this->getSpreadsheetColumnName($total_properties)}1";

        $header = [
            $column_registration_info => i::__('Informações sobre as inscrições e proponentes'),
            $column_evaluator_info => i::__('Informações sobre o avaliador'),
        ];

        // Parte dos dados da avaliação
        $data_header = $this->getEvaluationDataHeader($job, $total_properties);
        $header = isset($data_header['header']) ? array_merge($header, $data_header['header']) : $header;
        $sub_header = isset($data_header['subHeader']) ? array_merge($sub_header, $data_header['subHeader']) : $sub_header;
        $column_prefixes = $data_header['columnPrefixes'] ?? $this->generateSpreadsheetStructure(1, 300);

        // Parte do parecer/resultado da avaliação
        $result_header = $this->getEvaluationResultHeader($job, $properties, $column_prefixes);

        $header = isset($result_header['header']) ? array_merge($header, $result_header['header']) : $header;
        $sub_header = isset($result_header['subHeader']) ? array_merge($sub_header, $result_header['subHeader']) : $sub_header;

        // Parte da isenção por selos (duas colunas: "Isento" + rótulo configurado).
        // Só é adicionada quando a fase possui sealExemptionConfig ativa, evitando
        // colunas vazias em planilhas de fases sem a funcionalidade (spec-c49fa0bb §4.4).
        $exemption_header = $this->getSealExemptionHeader($job, $sub_header);
        if (isset($exemption_header['header'])) {
            $header = array_merge($header, $exemption_header['header']);
        }
        if (isset($exemption_header['subHeader'])) {
            $sub_header = array_merge($sub_header, $exemption_header['subHeader']);
        }

        $result = [$header, $sub_header];

        return $result;
    }

    protected function _getBatch(Job $job) : array {
        $app = App::i();
        
        $opportunity = $job->owner;

        $query = [];
        $query['@limit'] = $this->limit;
        $query['@page'] = $this->page;
        $query['@order'] = $job->query['@order'] ?? 'id ASC';
        $opportunity_controller = $app->controller('opportunity');
        $opportunity_controller->data = $opportunity_controller->postData;
        $evaluations = $opportunity_controller->apiFindEvaluations($opportunity->id, $query);
        $evaluations = json_decode(json_encode($evaluations), true);

        $result = $this->getEvaluationDataBatch($job, $evaluations);

        // Complementa cada linha com as colunas de isenção por selos (spec-c49fa0bb §4.4).
        // Os dados são resolvidos via query em lote sobre seal_exemption_status, de
        // forma desacoplada do @select da ApiQuery de inscrições.
        $this->appendSealExemptionColumns($job, $evaluations, $result);

        return $result;
    }

    /**
     * Constrói o cabeçalho das duas colunas de isenção por selos (spec-c49fa0bb §4.4):
     *  - sealExemption (booleana): cabeçalho "Isento", conteúdo Sim/Não.
     *  - sealExemptionLabel (textual): cabeçalho = rótulo configurado da fase,
     *    conteúdo = rótulo para isentos (vazio caso contrário).
     *
     * Retorna header/subHeader nulos quando a fase não tem sealExemptionConfig ativa,
     * para que as colunas não sejam adicionadas à planilha (evita colunas vazias).
     *
     * Segurança: nunca expõe IDs internos de selos — apenas o enum de status e o
     * rótulo textual de exibição.
     *
     * @param Job $job
     * @param array $sub_header Sub-cabeçalho acumulado até o momento (para calcular
     *                          a posição das novas colunas).
     * @return array{header: ?array, subHeader: ?array}
     */
    protected function getSealExemptionHeader(Job $job, array $sub_header): array
    {
        if (!$this->hasSealExemptionConfig($job)) {
            return ['header' => null, 'subHeader' => null];
        }

        // Rótulo sanitizado: protege contra caracteres especiais e injeção de
        // fórmula em CSV/Excel (cleanTextForExport escapa prefixes =+-@, normaliza
        // UTF-8 e remove caracteres de controle).
        $label = $this->cleanTextForExport($this->getSealExemptionLabel($job));
        if ($label === '') {
            $label = i::__('Isento por selos válidos');
        }

        // Posicionamento: próximas 2 colunas após as já definidas em $sub_header.
        $start = count($sub_header) + 1;
        $col_exempt = $this->getSpreadsheetColumnName($start);
        $col_label = $this->getSpreadsheetColumnName($start + 1);

        return [
            'header' => [
                "{$col_exempt}1:{$col_label}1" => i::__('Isenção por selos'),
            ],
            'subHeader' => [
                'sealExemption' => i::__('Isento'),
                // Cabeçalho da coluna textual = rótulo configurado da fase (spec §4.4).
                'sealExemptionLabel' => $label,
            ],
        ];
    }

    /**
     * Anexa as colunas de isenção por selos em cada linha do batch.
     *
     * - sealExemption: "Sim" quando seal_exemption_status = 'granted'; "Não" caso
     *   contrário (inclui agent_missing, null e demais estados).
     * - sealExemptionLabel: rótulo configurado da fase para isentos; vazio para
     *   não-isentos (evita redundância com o cabeçalho da coluna, que já é o rótulo).
     *
     * O status é resolvido por query em lote (1 query por página de batch), mapeado
     * por registration_id — robusto à ordenação. O rótulo é constante por fase
     * (lido do EMC, com fallback localizado).
     *
     * @param Job $job
     * @param array $evaluations Resultado de apiFindEvaluations (já normalizado p/ array).
     * @param array $result Linhas produzidas por getEvaluationDataBatch (modificado in-place).
     * @return void
     */
    protected function appendSealExemptionColumns(Job $job, array $evaluations, array &$result): void
    {
        if (empty($result)) {
            return;
        }

        // Só popula colunas quando a fase tem config ativa. Quando não tem, as
        // chaves não estarão no sub_header e seriam ignoradas pelo _execute; mas
        // evitamos a query desnecessária.
        if (!$this->hasSealExemptionConfig($job)) {
            return;
        }

        $app = App::i();
        $rows = $evaluations['evaluations'] ?? [];

        // Coleta os IDs das inscrições presentes nesta página.
        $reg_ids = [];
        foreach ($rows as $evaluation) {
            $reg_id = $evaluation['registration_id']
                ?? ($evaluation['registration']['id'] ?? null);
            if ($reg_id !== null) {
                $reg_ids[] = (int) $reg_id;
            }
        }
        $reg_ids = array_values(array_unique($reg_ids));

        // Map: registration_id => seal_exemption_status.
        // Usamos prepared statement (placeholders posicionais) — IDs já cast p/ int.
        $statuses = [];
        if ($reg_ids) {
            $conn = $app->em->getConnection();
            $placeholders = implode(',', array_fill(0, count($reg_ids), '?'));
            $sql = "SELECT id, seal_exemption_status FROM registration WHERE id IN ({$placeholders})";
            foreach ($conn->fetchAllAssociative($sql, $reg_ids) as $row) {
                $statuses[(int) $row['id']] = $row['seal_exemption_status'];
            }
        }

        $label = $this->cleanTextForExport($this->getSealExemptionLabel($job));

        // Zip por índice: $result segue a mesma ordem de $evaluations['evaluations']
        // (ambos iteram o mesmo array na mesma sequência em _getEvaluationDataBatch).
        // A busca do status é feita por registration_id, então é robusta mesmo se
        // a ordem eventualmente divergir.
        $count = min(count($result), count($rows));
        for ($i = 0; $i < $count; $i++) {
            $reg_id = $rows[$i]['registration_id']
                ?? ($rows[$i]['registration']['id'] ?? null);

            $status = ($reg_id !== null && isset($statuses[(int) $reg_id]))
                ? $statuses[(int) $reg_id]
                : null;
            $is_exempt = ($status === 'granted');

            $result[$i]['sealExemption'] = $is_exempt ? i::__('Sim') : i::__('Não');
            $result[$i]['sealExemptionLabel'] = $is_exempt ? $label : i::__('Não isenta');
        }
    }

    /**
     * Verifica se a fase possui configuração de isenção por selos ativa
     * (sealExemptionConfig com ao menos um selo).
     */
    protected function hasSealExemptionConfig(Job $job): bool
    {
        $opportunity = $job->owner;
        $emc = $opportunity->evaluationMethodConfiguration ?? null;
        return SealExemptionService::hasActiveConfig($emc?->sealExemptionConfig);
    }

    /**
     * Resolve o rótulo de isenção por selos da fase (do sealExemptionConfig do EMC),
     * com fallback localizado "Isento por selos válidos" (spec-c49fa0bb §3.1).
     */
    protected function getSealExemptionLabel(Job $job): string
    {
        $opportunity = $job->owner;
        $emc = $opportunity->evaluationMethodConfiguration ?? null;
        return SealExemptionService::getConfigLabel($emc?->sealExemptionConfig);
    }

    function getSpreadsheetColumnName($index) {
        $column_name = '';
        
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $column_name = chr($mod + 65) . $column_name;
            $index = (int)(($index - $mod) / 26);
        }

        return $column_name;
    }
    
    function generateSpreadsheetStructure($num_rows, $num_cols) {
        $sheet = [];
        for ($row = 1; $row <= $num_rows; $row++) {
            for ($col = 1; $col <= $num_cols; $col++) {
                $column_name = $this->getSpreadsheetColumnName($col);
                $cell_reference = $column_name;
                $sheet[] = $cell_reference;
            }
        }
        return $sheet;
    }

    protected function getEvaluatorSpreadsheetColumns(?array $valuer): array
    {
        if (!$valuer) {
            return [
                'committeeSequentialNumber' => '',
                'valuerUserId' => '',
                'valuerAgentId' => '',
                'user' => '',
            ];
        }

        $user = $valuer['user'] ?? null;
        if (is_array($user)) {
            $user_id = $user['id'] ?? null;
        } elseif (is_object($user)) {
            $user_id = $user->id ?? null;
        } else {
            $user_id = $user;
        }

        return [
            'committeeSequentialNumber' => $valuer['committeeSequentialNumber'] ?? '',
            'valuerUserId' => $user_id ?? '',
            'valuerAgentId' => $valuer['id'] ?? '',
            'user' => $valuer['name'] ?? '',
        ];
    }

    function statusName($status) {
        if($status === 10) {
            return i::__('Selecionada');
        } elseif($status === 8) {
            return i::__('Suplente');
        } elseif($status === 3) {
            return i::__('Não selecionada');
        } elseif($status === 2) {
            return i::__('Inválida');
        } elseif($status === 1) {
            return i::__('Pendente');
        } else {
            return i::__('Rascunho');
        }
    }

    function getEvaluationDataHeader(Job $job, int $total_properties)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataHeader:before", [$job, $total_properties]);

        $result = $this->_getEvaluationDataHeader($job, $total_properties);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataHeader:after", [$job, $total_properties, &$result]);

        return $result;
    }

    function getEvaluationResultHeader(Job $job, array $properties, array $column_prefixes)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationResultHeader:before", [$job, $properties, $column_prefixes]);

        $result = $this->_getEvaluationResultHeader($job, $properties, $column_prefixes);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationResultHeader:after", [$job, $properties, $column_prefixes, &$result]);

        return $result;
    }

    function getEvaluationDataBatch(Job $job, array $evaluations)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataBatch:before", [$job, $evaluations]);

        $result = $this->_getEvaluationDataBatch($job, $evaluations);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataBatch:after", [$job, $evaluations, &$result]);

        return $result;
    }

    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationDataHeader(Job $job, int $total_properties): array;
    
    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationResultHeader(Job $job, array $properties, array $column_prefixes): array;

    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationDataBatch(Job $job, array $evaluations): array;
}
