<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
use MapasCulturais\Entities\Seal;
use SealExemption\SealExemptionService;

$this->import('
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
                    $seal_validator_fields[$field_id]['validators'][$seal->id . ':' . $locked_field] = [
                        'sealId' => (int) $seal->id,
                        'sealName' => $seal->name,
                        'fieldName' => $locked_field,
                        'isInvalidator' => !empty($field_config['isInvalidator']),
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

?>

<div class="registration__actions">
    <div ref=header>
        <h2 class="regular primary__color"><?= i::__("Formulário de") ?> <strong><?= $evaluation_method_config_name ?></strong></h2>
        <?php if (!empty($infos["general"])): ?>
            <registration-evaluation-info :entity="entity"></registration-evaluation-info>
        <?php endif; ?>
    </div>
    <div class="evaluation-form scrollbar" ref="form">
        <div v-if="hasSealValidatorFields" class="evaluation-form__seal-alert">
            <div class="evaluation-form__seal-alert-header">
                <strong><?= i::__('Campos de selo para conferir') ?></strong>
                <span>{{ sealValidatorFields.length }} <?= i::__('campo(s)') ?></span>
            </div>
            <p><?= i::__('Confira estes campos antes de concluir a avaliação.') ?></p>
            <ul class="evaluation-form__seal-list">
                <li
                    v-for="field in sealValidatorFields"
                    :key="field.fieldId"
                    class="evaluation-form__seal-item"
                    :class="{ 'evaluation-form__seal-item--invalidator': field.hasInvalidator }"
                >
                    <span class="evaluation-form__seal-field">{{ field.fieldLabel }}</span>
                    <span class="evaluation-form__seal-kind">
                        {{ field.hasInvalidator ? '<?= i::__('Campo invalidador') ?>' : '<?= i::__('Campo validador') ?>' }}
                    </span>
                </li>
            </ul>
        </div>
        <?php $this->part("{$entity->opportunity->evaluationMethod->slug}/evaluation-form"); ?>
    </div>

    <div ref="buttons">
        <evaluation-actions :form-data="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
    </div>
</div>