<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\Entities\Registration $registration
 */

// Obter logo da plataforma
$logoUrl = '';
$logoImage = $app->config['logo.image'] ?? '';
if ($logoImage) {
    $logoPath = $app->view->resolveFilename('assets', $logoImage);
    if (file_exists($logoPath)) {
        $logoUrl = $logoPath;
    }
}

$siteName = $app->config['logo.title'] . ' ' . $app->config['logo.subtitle'];

$historyItems = $historyItems ?? [];
if (!$historyItems) {
    $historyItems[] = [
        'type' => 'data_collection',
        'title' => ($registration->opportunity->isFirstPhase ?? false) ? 'Inscrição' : $registration->opportunity->name,
        'opportunity' => $registration->opportunity,
        'registration' => $registration,
    ];
}

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$parseValue = static function ($value) {
    if (!is_string($value)) {
        return $value;
    }

    $trimmed = trim($value);
    if ($trimmed === '') {
        return $value;
    }

    if (($trimmed[0] === '{' && str_ends_with($trimmed, '}')) || ($trimmed[0] === '[' && str_ends_with($trimmed, ']'))) {
        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
    }

    return $value;
};

$normalizeValue = static function ($value) use ($parseValue, &$normalizeValue) {
    $value = $parseValue($value);

    if (is_object($value)) {
        $value = get_object_vars($value);
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = $normalizeValue($item);
        }
    }

    return $value;
};

$isAssoc = static function (array $value): bool {
    return array_keys($value) !== range(0, count($value) - 1);
};

$hasValue = static function ($value) use (&$hasValue, $normalizeValue): bool {
    $value = $normalizeValue($value);

    if ($value === null || $value === '') {
        return false;
    }

    if (is_array($value)) {
        foreach ($value as $item) {
            if ($hasValue($item)) {
                return true;
            }
        }
        return false;
    }

    return true;
};

$formatArrayLike = static function ($value) use (&$formatArrayLike, $normalizeValue, $isAssoc): string {
    $value = $normalizeValue($value);

    if (!is_array($value)) {
        return (string) $value;
    }

    $parts = [];
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            if ($isAssoc($item)) {
                $nested = [];
                foreach ($item as $nestedKey => $nestedValue) {
                    if ($nestedValue === true || $nestedValue === 'true' || $nestedValue === 1 || $nestedValue === '1') {
                        $nested[] = (string) $nestedKey;
                    } elseif ($nestedValue !== null && $nestedValue !== '' && !is_array($nestedValue)) {
                        $nested[] = (string) $nestedValue;
                    }
                }
                if ($nested) {
                    $parts[] = implode(', ', $nested);
                }
            } else {
                $nested = $formatArrayLike($item);
                if ($nested !== '') {
                    $parts[] = $nested;
                }
            }
        } elseif ($item === true || $item === 'true' || $item === 1 || $item === '1') {
            $parts[] = (string) $key;
        } elseif ($item !== null && $item !== '') {
            $parts[] = (string) $item;
        }
    }

    return implode(', ', $parts);
};

$getFieldConfig = static function ($field): array {
    $config = $field->config ?? [];
    if (is_object($config)) {
        $config = get_object_vars($config);
    }
    return is_array($config) ? $config : [];
};

$isTruthy = static function ($value): bool {
    return $value === true || $value === 1 || $value === '1' || $value === 'true';
};

$shouldDisplayField = static function ($field, $phaseRegistration) use ($isTruthy): bool {
    if (!$field || !$phaseRegistration) {
        return false;
    }

    if (!empty($field->categories) && !in_array($phaseRegistration->category, (array) $field->categories, true)) {
        return false;
    }

    if (!empty($field->registrationRanges) && !in_array($phaseRegistration->range, (array) $field->registrationRanges, true)) {
        return false;
    }

    if (!empty($field->proponentTypes) && !in_array($phaseRegistration->proponentType, (array) $field->proponentTypes, true)) {
        return false;
    }

    if (!empty($field->conditional)) {
        $fieldName = $field->conditionalField ?? null;
        $fieldValue = $field->conditionalValue ?? null;
        $currentValue = $fieldName ? ($phaseRegistration->$fieldName ?? null) : null;

        if (is_array($currentValue)) {
            return in_array($fieldValue, $currentValue, true);
        }

        if ($fieldName === 'appliedForQuota') {
            return $isTruthy($currentValue);
        }

        return $currentValue == $fieldValue;
    }

    return true;
};

$getPrimaryFile = static function ($phaseRegistration, string $groupName) {
    $file = $phaseRegistration->files[$groupName] ?? null;

    if (is_array($file)) {
        return $file[0] ?? null;
    }

    return $file;
};

$formatNumber = static function ($value): string {
    if ($value === null || $value === '') {
        return '';
    }

    if (!is_numeric($value)) {
        return (string) $value;
    }

    $formatted = number_format((float) $value, 2, ',', '.');
    $formatted = preg_replace('/,00$/', '', $formatted);
    $formatted = preg_replace('/(\,\d*[1-9])0$/', '$1', $formatted);

    return $formatted;
};

$statusClassByValue = static function ($value): string {
    return match ((string) $value) {
        '1', 'valid' => 'success',
        '-1', 'invalid' => 'danger',
        default => 'neutral',
    };
};

$statusLabelByValue = static function ($value): string {
    return match ((string) $value) {
        '1', 'valid' => 'Válido',
        '-1', 'invalid' => 'Inválido',
        default => (string) $value,
    };
};

$formatQualificationResult = static function ($result) use ($normalizeValue): string {
    $result = $normalizeValue($result);
    if (!is_array($result)) {
        return (string) $result;
    }

    $formatted = array_map(function ($value) {
        return match ($value) {
            'valid' => 'Atende',
            'invalid' => 'Não atende',
            'others' => null,
            default => (string) $value,
        };
    }, $result);

    $formatted = array_values(array_filter($formatted, fn ($value) => $value !== null && $value !== ''));

    return implode(' / ', $formatted);
};

$renderStatusBadge = static function (string $label, string $class) use ($escape): string {
    $normalizedClass = preg_replace('/[^a-z0-9_-]+/i', '', $class) ?: 'neutral';
    return '<span class="status-badge status-badge--' . $normalizedClass . '">' . $escape($label) . '</span>';
};

$renderEvaluationAttachments = static function ($evaluation) use ($escape): string {
    if (!$evaluation) {
        return '';
    }

    $files = $evaluation->files['evaluationAttachment'] ?? null;
    if (!$files) {
        return '';
    }

    if (!is_array($files)) {
        $files = [$files];
    }

    $html = '<div class="evaluation-subsection"><strong>Anexos da avaliação:</strong><ul>';
    foreach ($files as $file) {
        $html .= '<li>' . $escape($file->description ?: $file->name) . '</li>';
    }
    $html .= '</ul></div>';

    return $html;
};

$bankConfig = $app->config['module.registrationFieldTypes'] ?? [];
$bankTypes = $bankConfig['bank_types'] ?? [];
$accountTypes = $bankConfig['account_types'] ?? [];
$countryLabels = $app->view->jsObject['config']['countryLocalization']['labelsByCountry'] ?? [];

$renderFieldValue = static function ($field, $value) use (
    $escape,
    $normalizeValue,
    $formatArrayLike,
    $getFieldConfig,
    $hasValue,
    $bankTypes,
    $accountTypes,
    $countryLabels,
    $isTruthy
): string {
    $config = $getFieldConfig($field);
    $fieldType = $field->fieldType ?? 'field';
    $entityField = $config['entityField'] ?? null;
    $value = $normalizeValue($value);

    if ($fieldType === 'persons') {
        $people = is_array($value) ? $value : [];
        if (!$people) {
            return '<em>Nenhum dado informado.</em>';
        }

        $labels = [
            'name' => 'Nome',
            'fullName' => 'Nome completo',
            'socialName' => 'Nome social',
            'cpf' => 'CPF',
            'income' => 'Renda',
            'education' => 'Escolaridade',
            'telephone' => 'Telefone',
            'email' => 'Email',
            'race' => 'Raça/Cor',
            'gender' => 'Gênero',
            'sexualOrientation' => 'Orientação sexual',
            'deficiencies' => 'Deficiências',
            'comunty' => 'Comunidade tradicional',
            'area' => 'Áreas de atuação',
            'funcao' => 'Funções/Profissões',
            'relationship' => 'Relação',
            'function' => 'Função',
        ];

        $html = '';
        foreach ($people as $person) {
            if (!is_array($person) || !$person) {
                continue;
            }

            $personHtml = '';
            foreach ($labels as $key => $label) {
                if (empty($config[$key]) || !$hasValue($person[$key] ?? null)) {
                    continue;
                }

                $formatted = is_array($person[$key]) ? $formatArrayLike($person[$key]) : (string) $person[$key];
                if ($formatted === '') {
                    continue;
                }

                $personHtml .= '<div><strong>' . $escape($label) . ':</strong> ' . $escape($formatted) . '</div>';
            }

            if ($personHtml !== '') {
                $html .= '<div class="field-card">' . $personHtml . '</div>';
            }
        }

        return $html !== '' ? $html : '<em>Nenhum dado informado.</em>';
    }

    if ($fieldType === 'custom-table') {
        $rows = is_array($value) ? $value : [];
        $columns = $config['columns'] ?? [];
        if (!$rows || !$columns) {
            return '<em>Nenhum dado informado.</em>';
        }

        $html = '<table><thead><tr>';
        foreach ($columns as $column) {
            $column = is_array($column) ? $column : (array) $column;
            $html .= '<th>' . $escape($column['name'] ?? '-') . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $row = is_array($row) ? $row : (array) $row;
            $html .= '<tr>';
            foreach ($columns as $index => $column) {
                $html .= '<td>' . $escape($row["col{$index}"] ?? '-') . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    if ($entityField === '@location' && is_array($value)) {
        $country = $value['address_level0'] ?? 'BR';
        $html = '';
        foreach ($value as $key => $item) {
            if (in_array($key, ['location', 'publicLocation', 'En_Pais'], true) || str_starts_with((string) $key, 'field') || !$hasValue($item)) {
                continue;
            }

            $label = $countryLabels[$country][$key] ?? $countryLabels['BR'][$key] ?? $key;
            if ($key === 'address') {
                $label = 'Endereço completo';
            }

            $html .= '<div><strong>' . $escape($label) . ':</strong> ' . $escape($item) . '</div>';
        }

        if (array_key_exists('publicLocation', $value)) {
            $html .= '<div><strong>Este endereço pode ficar público na plataforma?:</strong> ' . ($isTruthy($value['publicLocation']) ? 'Sim' : 'Não') . '</div>';
        }

        return $html !== '' ? $html : '<em>Nenhum dado informado.</em>';
    }

    if (($entityField === '@links' || $fieldType === 'links') && is_array($value)) {
        $html = '';
        foreach ($value as $item) {
            $item = is_array($item) ? $item : (array) $item;
            if (!$hasValue($item['value'] ?? null)) {
                continue;
            }

            $title = $item['title'] ?? 'Link';
            $html .= '<div><strong>' . $escape($title) . ':</strong> ' . $escape($item['value']) . '</div>';
        }

        return $html !== '' ? $html : '<em>Nenhum dado informado.</em>';
    }

    if ($fieldType === 'bankFields' && is_array($value)) {
        $accountType = $accountTypes[$value['account_type'] ?? ''] ?? ($value['account_type'] ?? '');
        $bank = $bankTypes[$value['number'] ?? ''] ?? ($value['number'] ?? '');

        $lines = [
            '<div><strong>Típo de conta:</strong> ' . $escape($accountType) . '</div>',
            '<div><strong>Banco:</strong> ' . $escape($bank) . '</div>',
            '<div><strong>Agencia:</strong> ' . $escape($value['branch'] ?? '') . ' - ' . $escape($value['dv_branch'] ?? '') . '</div>',
            '<div><strong>Conta:</strong> ' . $escape($value['account_number'] ?? '') . ' - ' . $escape($value['dv_account_number'] ?? '') . '</div>',
        ];

        return implode('', $lines);
    }

    if (is_array($value)) {
        $formatted = $formatArrayLike($value);
        return $formatted !== '' ? $escape($formatted) : '<em>Nenhum dado informado.</em>';
    }

    return nl2br($escape($value));
};
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11pt; 
            line-height: 1.5;
            color: #333;
        }
        .logo-container { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .logo-container img { 
            max-width: 250px; 
            max-height: 80px; 
        }
        .logo-container .site-name { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #222; 
            margin: 10px 0;
        }
        .header { margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18pt; color: #222; }
        .info-block { margin: 20px 0; }
        .info-block h2 { font-size: 14pt; color: #444; margin: 15px 0 10px; }
        .field { margin: 10px 0; padding: 10px; background: #f9f9f9; }
        .field-card { margin-top: 8px; padding: 8px; background: #fff; border: 1px solid #e2e2e2; }
        .field-label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
        .field-value { color: #333; }
        .phase-title { margin: 25px 0 10px; font-size: 12pt; color: #222; }
        .attachment-icon { margin-right: 5px; }
        .attachment-link { text-decoration: underline; color: #0066cc; }
        .history-item { margin-bottom: 24px; }
        .history-item + .history-item { border-top: 1px solid #ddd; padding-top: 20px; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 9pt; font-weight: bold; }
        .status-badge--success { background: #e6f6ea; color: #1d6b35; }
        .status-badge--danger { background: #fdeaea; color: #a12626; }
        .status-badge--warning { background: #fff4db; color: #8a5a00; }
        .status-badge--neutral { background: #ececec; color: #555; }
        .evaluation-card { margin: 12px 0; padding: 12px; background: #f8f8f8; border: 1px solid #e1e1e1; }
        .evaluation-card h4 { margin: 0 0 8px; font-size: 11pt; }
        .evaluation-meta { margin: 6px 0 10px; }
        .evaluation-meta p { margin: 4px 0; }
        .evaluation-subsection { margin-top: 10px; }
        .evaluation-subsection h5 { margin: 0 0 6px; font-size: 10pt; }
        .evaluation-subsection p { margin: 4px 0; }
        .evaluation-subsection ul { margin: 6px 0 0 18px; padding: 0; }
        .evaluation-subsection li { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #e9e9e9; font-weight: bold; }
    </style>
</head>
<body>

<?php $this->applyTemplateHook('registration-pdf', 'before'); ?>

<!-- Logo da plataforma -->
<?php $this->applyTemplateHook('registration-pdf-logo', 'before'); ?>
<div class="logo-container">
    <?php if ($logoUrl && file_exists($logoUrl)): ?>
        <?php
        $imageData = base64_encode(file_get_contents($logoUrl));
        $mimeType = mime_content_type($logoUrl);
        ?>
        <img src="data:<?= $mimeType ?>;base64,<?= $imageData ?>" alt="Logo">
    <?php else: ?>
        <div class="site-name"><?= htmlspecialchars($siteName) ?></div>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-logo', 'after'); ?>

<!-- Cabeçalho -->
<?php $this->applyTemplateHook('registration-pdf-header', 'before'); ?>
<div class="header">
    <h1>Ficha de Inscrição #<?= htmlspecialchars($registration->number) ?></h1>
    <p><strong>Oportunidade:</strong> <?= htmlspecialchars($registration->opportunity->name) ?></p>
    <p><strong>Responsável:</strong> <?= htmlspecialchars($registration->owner->name) ?></p>
    <?php if ($registration->sentTimestamp): ?>
        <p><strong>Data de envio:</strong> <?= $registration->sentTimestamp->format('d/m/Y H:i') ?></p>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-header', 'after'); ?>

<!-- Informações básicas -->
<?php $this->applyTemplateHook('registration-pdf-basic-info', 'before'); ?>
<div class="info-block">
    <h2>Informações Básicas</h2>
    
    <?php if ($registration->category): ?>
        <div class="field">
            <span class="field-label">Categoria:</span>
            <span class="field-value"><?= htmlspecialchars($registration->category) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($registration->range): ?>
        <div class="field">
            <span class="field-label">Faixa/Linha:</span>
            <span class="field-value"><?= htmlspecialchars($registration->range) ?></span>
        </div>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-basic-info', 'after'); ?>

<!-- Campos personalizados -->
<?php $this->applyTemplateHook('registration-pdf-fields', 'before'); ?>
<div class="info-block">
    <h2>Dados do Formulário</h2>

    <?php foreach ($historyItems as $index => $historyItem): ?>
        <?php
        $itemType = $historyItem['type'] ?? 'data_collection';
        $phaseTitle = $historyItem['title'] ?? 'Etapa';
        ?>

        <div class="history-item history-item--<?= $escape($itemType) ?>">
            <h3 class="phase-title"><?= $escape($phaseTitle) ?></h3>

            <?php if ($itemType === 'data_collection'): ?>
                <?php
                $phaseRegistration = $historyItem['registration'];
                $opportunity = $historyItem['opportunity'];
                $opportunity->registerRegistrationMetadata();

                $allFields = [];

                foreach ($opportunity->registrationFieldConfigurations as $field) {
                    $allFields[] = [
                        'type' => 'field',
                        'config' => $field,
                        'order' => $field->displayOrder ?? 999,
                    ];
                }

                foreach ($opportunity->registrationFileConfigurations as $fileConfig) {
                    $allFields[] = [
                        'type' => 'file',
                        'config' => $fileConfig,
                        'order' => $fileConfig->displayOrder ?? 999,
                    ];
                }

                usort($allFields, function ($a, $b) {
                    return $a['order'] <=> $b['order'];
                });
                ?>

                <?php foreach ($allFields as $item): ?>
                    <?php
                    $config = $item['config'];
                    if (!$shouldDisplayField($config, $phaseRegistration)) {
                        continue;
                    }

                    if ($item['type'] === 'file') {
                        $file = $getPrimaryFile($phaseRegistration, $config->fileGroupName);
                        if (!$file) {
                            continue;
                        }
                        ?>
                        <div class="field">
                            <span class="field-label"><?= $config->required ? '* ' : '' ?><?= $escape($config->title) ?>:</span>
                            <div class="field-value">
                                <span class="attachment-link"><span class="attachment-icon">▸</span><?= $escape($file->description ?: $file->name) ?></span>
                            </div>
                        </div>
                        <?php
                        continue;
                    }

                    $fieldName = $config->fieldName;
                    $value = $phaseRegistration->$fieldName ?? null;

                    if (($config->fieldType ?? null) !== 'section' && !$hasValue($value)) {
                        continue;
                    }
                    ?>

                    <?php if (($config->fieldType ?? null) === 'section'): ?>
                        <h4 class="phase-title"><?= $escape($config->title) ?></h4>
                    <?php else: ?>
                        <div class="field">
                            <span class="field-label"><?= $config->required ? '* ' : '' ?><?= $escape($config->title) ?>:</span>
                            <div class="field-value"><?= $renderFieldValue($config, $value) ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $evaluationSlug = $historyItem['evaluationMethodSlug'] ?? '';
                $consolidatedDetails = $normalizeValue($historyItem['consolidatedDetails'] ?? []);
                $evaluations = $historyItem['evaluations'] ?? [];
                ?>

                <div class="field">
                    <span class="field-label">Situação da fase:</span>
                    <div class="field-value"><?= $renderStatusBadge($historyItem['statusLabel'] ?? 'Sem status', $historyItem['statusClass'] ?? 'neutral') ?></div>
                </div>

                <?php if (!empty($historyItem['showResults'])): ?>
                    <?php if ($evaluationSlug === 'technical'): ?>
                        <div class="field">
                            <span class="field-label">Pontuação final:</span>
                            <div class="field-value"><?= $escape($formatNumber($historyItem['consolidatedResult'] ?? '')) ?></div>
                        </div>

                        <?php if (!empty($consolidatedDetails['appliedPointReward'])): ?>
                            <?php $policy = $normalizeValue($consolidatedDetails['appliedPointReward']); ?>
                            <div class="evaluation-card">
                                <h4>Bônus por pontuação</h4>
                                <div class="evaluation-subsection">
                                    <p><strong>Pontuação original:</strong> <?= $escape($formatNumber($policy['raw'] ?? '')) ?></p>
                                    <p><strong>Percentual aplicado:</strong> <?= $escape($formatNumber($policy['percentage'] ?? '')) ?>%</p>
                                    <?php if (!empty($policy['rules']) && is_array($policy['rules'])): ?>
                                        <ul>
                                            <?php foreach ($policy['rules'] as $rule): ?>
                                                <?php $rule = $normalizeValue($rule); ?>
                                                <li><?= $escape($rule['field']['title'] ?? 'Regra') ?>: <?= $escape((string) ($rule['value'] ?? '')) ?> (+<?= $escape($formatNumber($rule['percentage'] ?? '')) ?>%)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php $consolidatedLabel = $historyItem['consolidatedResultLabel'] ?? ''; ?>
                        <?php if ($consolidatedLabel !== '' && $consolidatedLabel !== null): ?>
                            <div class="field">
                                <span class="field-label">Resultado consolidado:</span>
                                <div class="field-value"><?= $escape((string) $consolidatedLabel) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="field">
                        <span class="field-label">Resultado da avaliação:</span>
                        <div class="field-value"><em>Resultado indisponível no momento.</em></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($historyItem['showDetails']) && $evaluations): ?>
                    <?php foreach ($evaluations as $evaluationIndex => $evaluationItem): ?>
                        <?php
                        $detail = $normalizeValue($evaluationItem['detail'] ?? []);
                        $evaluationEntity = $evaluationItem['evaluation'] ?? null;
                        $valuerName = $evaluationItem['valuerName'] ?? ('Parecerista #' . ($evaluationIndex + 1));
                        $evaluationResultLabel = $evaluationItem['resultLabel'] ?? '';
                        ?>
                        <div class="evaluation-card">
                            <h4><?= $escape($valuerName) ?></h4>
                            <div class="evaluation-meta">
                                <p><strong>Status da avaliação:</strong> <?= $escape($evaluationItem['statusLabel'] ?? 'Sem status') ?></p>
                                <?php if ($evaluationResultLabel !== ''): ?>
                                    <p><strong>Resultado:</strong> <?= $escape((string) $evaluationResultLabel) ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($detail['obs'])): ?>
                                <div class="evaluation-subsection">
                                    <h5><?= in_array($evaluationSlug, ['simple', 'continuous'], true) ? 'Justificativa' : 'Parecer' ?></h5>
                                    <p><?= nl2br($escape($detail['obs'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($evaluationSlug === 'technical' && !empty($detail['scores']) && is_array($detail['scores'])): ?>
                                <div class="evaluation-subsection">
                                    <h5>Detalhamento da pontuação</h5>
                                    <ul>
                                        <?php foreach ($detail['scores'] as $section): ?>
                                            <?php $section = $normalizeValue($section); ?>
                                            <li>
                                                <?= $escape($section['name'] ?? 'Seção') ?>:
                                                <strong><?= $escape($formatNumber($section['score'] ?? '')) ?></strong>
                                                <?php if (isset($section['maxScore'])): ?>
                                                    <em>(pontuação máxima: <?= $escape($formatNumber($section['maxScore'])) ?>)</em>
                                                <?php endif; ?>
                                                <?php if (!empty($section['criteria']) && is_array($section['criteria'])): ?>
                                                    <ul>
                                                        <?php foreach ($section['criteria'] as $criterion): ?>
                                                            <?php $criterion = $normalizeValue($criterion); ?>
                                                            <li>
                                                                <?= $escape($criterion['title'] ?? $criterion['name'] ?? 'Critério') ?>:
                                                                <strong><?= $escape($formatNumber($criterion['score'] ?? '')) ?></strong>
                                                                <?php if (isset($criterion['maxScore'])): ?>
                                                                    <em>(pontuação máxima: <?= $escape($formatNumber($criterion['maxScore'])) ?>)</em>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ($evaluationSlug === 'qualification' && !empty($detail['scores']) && is_array($detail['scores'])): ?>
                                <div class="evaluation-subsection">
                                    <h5>Detalhamento da avaliação</h5>
                                    <ul>
                                        <?php foreach ($detail['scores'] as $section): ?>
                                            <?php $section = $normalizeValue($section); ?>
                                            <li>
                                                <?= $escape($section['name'] ?? 'Seção') ?>
                                                <?php if (!empty($section['criteria']) && is_array($section['criteria'])): ?>
                                                    <ul>
                                                        <?php foreach ($section['criteria'] as $criterion): ?>
                                                            <?php $criterion = $normalizeValue($criterion); ?>
                                                            <li>
                                                                <?= $escape($criterion['name'] ?? $criterion['title'] ?? 'Critério') ?>:
                                                                <strong><?= $escape($formatQualificationResult($criterion['result'] ?? [])) ?></strong>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ($evaluationSlug === 'documentary' && !empty($detail['evaluations']) && is_array($detail['evaluations'])): ?>
                                <div class="evaluation-subsection">
                                    <h5>Detalhamento</h5>
                                    <?php foreach ($detail['evaluations'] as $documentaryItem): ?>
                                        <?php $documentaryItem = $normalizeValue($documentaryItem); ?>
                                        <div class="field-card">
                                            <p><?= $renderStatusBadge($statusLabelByValue($documentaryItem['evaluation'] ?? ''), $statusClassByValue($documentaryItem['evaluation'] ?? '')) ?></p>
                                            <p><strong><?= $escape($documentaryItem['label'] ?? 'Campo') ?></strong></p>
                                            <?php if (!empty($documentaryItem['obs'])): ?>
                                                <p><strong>Parecer:</strong> <?= nl2br($escape($documentaryItem['obs'])) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($documentaryItem['obs_items'])): ?>
                                                <p><strong>Detalhamento:</strong> <?= nl2br($escape($documentaryItem['obs_items'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (in_array($evaluationSlug, ['simple', 'continuous'], true)): ?>
                                <?= $renderEvaluationAttachments($evaluationEntity) ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-fields', 'after'); ?>

<!-- Agentes relacionados -->
<?php if (count($registration->relatedAgents) > 0): ?>
    <?php $this->applyTemplateHook('registration-pdf-agents', 'before'); ?>
    <div class="info-block">
        <h2>Agentes Relacionados</h2>
        
        <?php foreach ($registration->relatedAgents as $group => $agents): ?>
            <?php foreach ($agents as $agent): ?>
                <div class="field">
                    <span class="field-label"><?= htmlspecialchars($group) ?>:</span>
                    <span class="field-value"><?= htmlspecialchars($agent->name) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php $this->applyTemplateHook('registration-pdf-agents', 'after'); ?>
<?php endif; ?>

<?php $this->applyTemplateHook('registration-pdf', 'after'); ?>

</body>
</html>
