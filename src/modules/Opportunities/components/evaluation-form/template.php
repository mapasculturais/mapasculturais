<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
use MapasCulturais\Entities\Seal;
use SealExemption\SealExemptionService;

$this->import('
    mc-icon
    registration-evaluation-info
    evaluation-actions
');

/** @var MapasCulturais\Entities\Registration */
$entity = $this->controller->requestedEntity;

$opportunity = $entity->opportunity;
$evaluation_method_config_name = $opportunity->evaluationMethodConfiguration->name;
$infos = (array) $opportunity->evaluationMethodConfiguration->infos;
$evaluation_method_slug = $opportunity->evaluationMethod->slug;

$seal_validator_fields = [];
$seal_ids = SealExemptionService::getConfiguredSealIds($opportunity->evaluationMethodConfiguration->sealExemptionConfig);

if ($evaluation_method_slug !== 'technical' && $seal_ids) {
    $owner_field_seal_statuses = (array) $entity->owner->fieldSealStatuses;
    $related_agents = (array) ($entity->relatedAgents ?: []);
    $collective_field_seal_statuses = isset($related_agents['coletivo']) ? (array) $related_agents['coletivo'][0]->fieldSealStatuses : [];
    $seal_relations_by_seal_id = [];

    $agent_seal_relations = $entity->owner->getSealRelations();
    if (isset($related_agents['coletivo'])) {
        $agent_seal_relations = array_merge($agent_seal_relations, $related_agents['coletivo'][0]->getSealRelations());
    }

    foreach ($agent_seal_relations as $relation) {
        $seal_relations_by_seal_id[(int) $relation->seal->id] = $relation;
    }

    $field_entity_name = function ($field): ?string {
        if (!in_array($field->fieldType, ['agent-owner-field', 'agent-collective-field', 'space-field'], true)) {
            return null;
        }

        $config = (array) ($field->config ?? []);
        $entity_field = $config['entityField'] ?? null;

        return is_string($entity_field) && $entity_field !== '' ? $entity_field : null;
    };

    $locked_field_matches = function (string $locked_field, string $entity_field): bool {
        $parts = explode('.', $locked_field, 2);
        $field_name = $parts[1] ?? $locked_field;

        return $field_name === $entity_field;
    };

    $field_status_for_validator = function ($field, string $locked_field, int $seal_id) use ($owner_field_seal_statuses, $collective_field_seal_statuses): array {
        $config = (array) ($field->config ?? []);
        $entity_field = $config['entityField'] ?? null;

        if (!is_string($entity_field) || $entity_field === '') {
            return [];
        }

        $statuses = [];
        if ($field->fieldType === 'agent-owner-field') {
            $statuses = (array) ($owner_field_seal_statuses[$entity_field] ?? []);
        } elseif ($field->fieldType === 'agent-collective-field') {
            $statuses = (array) ($collective_field_seal_statuses[$entity_field] ?? []);
        }

        foreach ($statuses as $status) {
            $status = (array) $status;
            if ((int) ($status['sealId'] ?? 0) === $seal_id && ($status['fieldName'] ?? null) === $locked_field) {
                return $status;
            }
        }

        return [];
    };

    $format_date = function ($date): ?string {
        if ($date instanceof \DateTimeInterface) {
            return $date->format(i::__('d/m/Y'));
        }

        return null;
    };

    $calculate_field_validity = function (array $field_config, $relation_date) use ($format_date): array {
        if (empty($field_config['hasExpiry']) || empty($field_config['periodValue']) || empty($field_config['periodUnit']) || !$relation_date instanceof \DateTimeInterface) {
            return [
                'fieldStatus' => 'no_expiration',
                'expiryDate' => null,
                'isUnlocked' => false,
                'isLocked' => true,
            ];
        }

        $expiry_date = \DateTimeImmutable::createFromInterface($relation_date);
        $period_value = (int) $field_config['periodValue'];

        switch ($field_config['periodUnit']) {
            case 'day':
                $expiry_date = $expiry_date->modify("+{$period_value} days");
                break;
            case 'year':
                $expiry_date = $expiry_date->modify("+{$period_value} years");
                break;
            case 'month':
            default:
                $expiry_date = $expiry_date->modify("+{$period_value} months");
                break;
        }

        $today = new \DateTimeImmutable('today');
        $expiry_day = \DateTimeImmutable::createFromFormat('!Y-m-d', $expiry_date->format('Y-m-d'));
        $warning_day = $expiry_day->modify('-7 days');

        if ($expiry_day < $today) {
            $field_status = 'expired';
        } elseif ($warning_day <= $today) {
            $field_status = 'about_to_expire';
        } else {
            $field_status = 'valid';
        }

        return [
            'fieldStatus' => $field_status,
            'expiryDate' => $format_date($expiry_date),
            'isUnlocked' => in_array($field_status, ['about_to_expire', 'expired'], true),
            'isLocked' => in_array($field_status, ['valid', 'no_expiration'], true),
        ];
    };

    foreach ($seal_ids as $seal_id) {
        $seal = $app->repo('Seal')->find($seal_id);
        if (!$seal instanceof Seal) {
            continue;
        }

        foreach ((array) $seal->lockedFieldsConfig as $locked_field => $field_config) {
            $field_config = (array) $field_config;

            foreach ($opportunity->allPhases as $phase) {
                foreach ($phase->registrationFieldConfigurations as $field) {
                    $entity_field = $field_entity_name($field);
                    if (!$entity_field || !$locked_field_matches($locked_field, $entity_field)) {
                        continue;
                    }

                    $field_id = (int) $field->id;
                    $seal_validator_fields[$field_id] ??= [
                        'fieldId' => $field_id,
                        'fieldName' => $field->fieldName,
                        'fieldLabel' => $field->title,
                        'hasInvalidator' => false,
                        'validators' => [],
                    ];
                    $seal_validator_fields[$field_id]['hasInvalidator'] = $seal_validator_fields[$field_id]['hasInvalidator'] || !empty($field_config['isInvalidator']);
                    $field_status = $field_status_for_validator($field, $locked_field, (int) $seal->id);
                    $seal_relation = $seal_relations_by_seal_id[(int) $seal->id] ?? [];
                    $relation_date = $seal_relation ? ($seal_relation->validateDate ?: $seal_relation->createTimestamp) : null;
                    $calculated_validity = $calculate_field_validity($field_config, $relation_date);
                    if (empty($field_status['expiryDate']) && !empty($calculated_validity['expiryDate'])) {
                        $field_status = array_merge($field_status, $calculated_validity);
                    } else {
                        $field_status = array_merge($calculated_validity, $field_status);
                    }
                    $seal_validator_fields[$field_id]['validators'][$seal->id . ':' . $locked_field] = [
                        'sealId' => (int) $seal->id,
                        'sealName' => $seal->name,
                        'fieldName' => $locked_field,
                        'fieldStatus' => $field_status['fieldStatus'] ?? 'no_expiration',
                        'expiryDate' => $field_status['expiryDate'] ?? null,
                        'isInvalidator' => $field_status['isInvalidator'] ?? !empty($field_config['isInvalidator']),
                        'isUnlocked' => $field_status['isUnlocked'] ?? false,
                        'isLocked' => $field_status['isLocked'] ?? true,
                        'hasSealRelation' => (bool) $seal_relation,
                        'validateDate' => $format_date($relation_date),
                        'createTimestamp' => $seal_relation ? $seal_relation->createTimestamp : null,
                        'files' => [
                            'avatar' => $seal->files['avatar'] ?? null,
                        ],
                    ];
                }
            }
        }
    }

    foreach ($seal_validator_fields as &$validator_field) {
        $validator_field['validators'] = array_values($validator_field['validators']);
    }
    unset($validator_field);
}

$seal_validator_fields = array_values($seal_validator_fields);

$this->jsObject['config']['evaluationFormSealValidators'] = [
    'enabled' => $evaluation_method_slug !== 'technical' && $entity->sealExemptionStatus !== 'granted' && !empty($seal_validator_fields),
    'fields' => $seal_validator_fields,
];

// Avaliador da URL (user:X) — se for o proponente, bloqueia o formulário
$valuer_user = $app->repo('User')->find($this->controller->data['user'] ?? -1) ?: $app->user;
$is_own_registration = $entity->owner->user->equals($valuer_user);

?>

<div class="registration__actions<?= $is_own_registration ? ' registration__actions--blocked' : '' ?>">
    <div ref="header">
        <h2 class="regular primary__color"><?= i::__("Formulário de") ?> <strong><?= $evaluation_method_config_name ?></strong></h2>
        <?php if ($is_own_registration): ?>
            <style>
                .evaluation-form__own-registration-notice {
                    display: flex;
                    align-items: flex-start;
                    gap: 0.75rem;
                    margin: 0.75rem 0 1rem;
                    padding: 0.875rem 1rem;
                    border-radius: 0.5rem;
                    border: 1px solid var(--mc-warning-500, #ff9f1c);
                    border-left: 4px solid var(--mc-warning-700, #f07b07);
                    background: linear-gradient(135deg, #fff8e6 0%, #fff3d6 100%);
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                }
                .evaluation-form__own-registration-notice .iconify {
                    flex-shrink: 0;
                    margin-top: 0.125rem;
                    color: var(--mc-warning-700, #f07b07);
                    font-size: 1.25rem;
                }
                .evaluation-form__own-registration-notice__text {
                    display: flex;
                    flex-direction: column;
                    gap: 0.25rem;
                    min-width: 0;
                }
                .evaluation-form__own-registration-notice__text strong {
                    color: var(--mc-gray-900, #1a1a1a);
                    font-size: 0.875rem;
                    line-height: 1.35;
                }
                .evaluation-form__own-registration-notice__text p {
                    margin: 0;
                    color: var(--mc-gray-700, #444);
                    font-size: 0.75rem;
                    line-height: 1.45;
                }
            </style>
            <div class="evaluation-form__own-registration-notice">
                <mc-icon name="exclamation"></mc-icon>
                <div class="evaluation-form__own-registration-notice__text">
                    <strong><?= i::__('Você não pode avaliar a própria inscrição') ?></strong>
                    <p><?= i::__('Esta inscrição pertence a você. A avaliação deve ser feita pelos demais membros da comissão.') ?></p>
                </div>
            </div>
        <?php elseif (!empty($infos["general"])): ?>
            <registration-evaluation-info :entity="entity"></registration-evaluation-info>
        <?php endif; ?>
    </div>

    <div class="evaluation-form scrollbar<?= $is_own_registration ? ' evaluation-form--disabled' : '' ?>" ref="form"<?= $is_own_registration ? ' style="opacity:0.65;pointer-events:none;user-select:none"' : '' ?>>
        <?php if ($is_own_registration): ?>
            <fieldset disabled>
                <?php $this->part("{$entity->opportunity->evaluationMethod->slug}/evaluation-form"); ?>
            </fieldset>
        <?php else: ?>
            <?php $this->part("{$entity->opportunity->evaluationMethod->slug}/evaluation-form"); ?>
        <?php endif; ?>
    </div>

    <?php if (!$is_own_registration): ?>
        <div ref="buttons">
            <evaluation-actions :form-data="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
        </div>
    <?php endif; ?>
</div>
