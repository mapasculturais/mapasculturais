<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV1\Theme $this
 */

use MapasCulturais\i;

$props = $this->getLockedFieldsSeal();
$agent_taxonomies = $app->getRegisteredTaxonomies(MapasCulturais\Entities\Agent::class);
$space_taxonomies = $app->getRegisteredTaxonomies(MapasCulturais\Entities\Space::class);

$lockedFieldsConfig = [];
if (property_exists($entity, 'lockedFieldsConfig') && $entity->lockedFieldsConfig) {
    $lockedFieldsConfig = is_array($entity->lockedFieldsConfig) ? $entity->lockedFieldsConfig : json_decode((string)$entity->lockedFieldsConfig, true);
    if (!is_array($lockedFieldsConfig)) {
        $lockedFieldsConfig = [];
    }
}
?>
<div id="locked-fields">
    <p class="alert info"><?= i::__('Selecione abaixo os campos que devem ser bloqueados e configure a validade por campo.') ?> </p>
    <input class="js-editable" style="display:none;" id="locked-fields-input" data-value="[]" type="hidden" data-edit="lockedFields" />
    <input type="hidden" id="locked-fields-config-input" class="js-include-editable" data-value="" />

    <form class="js-locked-fields">
        <div class="fields">
            <h2><?php $this->dict('entities: Agents') ?></h2>
            <div class="locked-fields-list">
                <?php foreach ($props['agent'] as $field => $values) : ?>
                    <?php
                        $field = $values['@select'] ?? $field;
                        $fieldKey = "agent.{$field}";
                        $isLocked = in_array($fieldKey, $entity->lockedFields);
                        $config = $lockedFieldsConfig[$fieldKey] ?? [];
                        $hasExpiry = !empty($config['hasExpiry']);
                        $periodValue = $config['periodValue'] ?? '';
                        $periodUnit = $config['periodUnit'] ?? 'month';
                        $isInvalidator = !empty($config['isInvalidator']);
                    ?>
                    <div class="locked-field-card" data-field="<?= htmlspecialchars($fieldKey) ?>">
                        <div class="locked-field-header">
                            <label>
                                <input type="checkbox" name="lockedFields[]" value="<?= htmlspecialchars($fieldKey) ?>" <?= $isLocked ? 'checked' : '' ?>>
                                <?= htmlspecialchars($values['label']) ?>
                            </label>
                            <button type="button" class="locked-field-toggle" aria-expanded="<?= $isLocked ? 'true' : 'false' ?>">
                                <span class="screen-reader-text"><?= i::__('Configurar validade') ?></span>
                            </button>
                        </div>
                        <div class="locked-field-body" <?= $isLocked ? '' : 'hidden' ?>>
                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="has-expiry" <?= $hasExpiry ? 'checked' : '' ?>>
                                <?= i::__('Este campo tem prazo de validade?') ?>
                            </label>

                            <div class="expiry-inputs" <?= $hasExpiry ? '' : 'hidden' ?>>
                                <label>
                                    <?= i::__('Duração') ?>:
                                    <input type="number" class="period-value" min="1" value="<?= htmlspecialchars((string)$periodValue) ?>">
                                </label>
                                <select class="period-unit">
                                    <option value="day" <?= $periodUnit === 'day' ? 'selected' : '' ?>><?= i::__('Dia(s)') ?></option>
                                    <option value="month" <?= $periodUnit === 'month' ? 'selected' : '' ?><?= i::__('Mês(es)') ?></option>
                                    <option value="year" <?= $periodUnit === 'year' ? 'selected' : '' ?><?= i::__('Ano(s)') ?></option>
                                </select>
                            </div>

                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="is-invalidator" <?= $isInvalidator ? 'checked' : '' ?> <?= $hasExpiry ? '' : 'disabled' ?>>
                                <?= i::__('Este campo é invalidador?') ?>
                            </label>
                            <p class="registration-help invalidator-help"><?= i::__('Se um campo invalidador expirar, o selo inteiro será considerado inválido, mesmo que outros campos estejam dentro do prazo.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <h3><?= i::__('Taxonomias') ?></h3>
                <?php foreach ($agent_taxonomies as $slug => $def) : ?>
                    <?php
                        $fieldKey = "agent.terms:{$slug}";
                        $isLocked = in_array($fieldKey, $entity->lockedFields);
                        $config = $lockedFieldsConfig[$fieldKey] ?? [];
                        $hasExpiry = !empty($config['hasExpiry']);
                        $periodValue = $config['periodValue'] ?? '';
                        $periodUnit = $config['periodUnit'] ?? 'month';
                        $isInvalidator = !empty($config['isInvalidator']);
                    ?>
                    <div class="locked-field-card" data-field="<?= htmlspecialchars($fieldKey) ?>">
                        <div class="locked-field-header">
                            <label>
                                <input type="checkbox" name="lockedFields[]" value="<?= htmlspecialchars($fieldKey) ?>" <?= $isLocked ? 'checked' : '' ?>>
                                <?= htmlspecialchars($def->description ?: $slug) ?>
                            </label>
                            <button type="button" class="locked-field-toggle" aria-expanded="<?= $isLocked ? 'true' : 'false' ?>">
                                <span class="screen-reader-text"><?= i::__('Configurar validade') ?></span>
                            </button>
                        </div>
                        <div class="locked-field-body" <?= $isLocked ? '' : 'hidden' ?>>
                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="has-expiry" <?= $hasExpiry ? 'checked' : '' ?>>
                                <?= i::__('Este campo tem prazo de validade?') ?>
                            </label>

                            <div class="expiry-inputs" <?= $hasExpiry ? '' : 'hidden' ?>>
                                <label>
                                    <?= i::__('Duração') ?>:
                                    <input type="number" class="period-value" min="1" value="<?= htmlspecialchars((string)$periodValue) ?>">
                                </label>
                                <select class="period-unit">
                                    <option value="day" <?= $periodUnit === 'day' ? 'selected' : '' ?><?= i::__('Dia(s)') ?></option>
                                    <option value="month" <?= $periodUnit === 'month' ? 'selected' : '' ?><?= i::__('Mês(es)') ?></option>
                                    <option value="year" <?= $periodUnit === 'year' ? 'selected' : '' ?><?= i::__('Ano(s)') ?></option>
                                </select>
                            </div>

                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="is-invalidator" <?= $isInvalidator ? 'checked' : '' ?> <?= $hasExpiry ? '' : 'disabled' ?>>
                                <?= i::__('Este campo é invalidador?') ?>
                            </label>
                            <p class="registration-help invalidator-help"><?= i::__('Se um campo invalidador expirar, o selo inteiro será considerado inválido, mesmo que outros campos estejam dentro do prazo.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="fields">
            <h2><?php $this->dict('entities: Spaces') ?></h2>
            <div class="locked-fields-list">
                <?php foreach ($props['space'] as $field => $values) : ?>
                    <?php
                        $fieldKey = "space.{$field}";
                        $isLocked = in_array($fieldKey, $entity->lockedFields);
                        $config = $lockedFieldsConfig[$fieldKey] ?? [];
                        $hasExpiry = !empty($config['hasExpiry']);
                        $periodValue = $config['periodValue'] ?? '';
                        $periodUnit = $config['periodUnit'] ?? 'month';
                        $isInvalidator = !empty($config['isInvalidator']);
                    ?>
                    <div class="locked-field-card" data-field="<?= htmlspecialchars($fieldKey) ?>">
                        <div class="locked-field-header">
                            <label>
                                <input type="checkbox" name="lockedFields[]" value="<?= htmlspecialchars($fieldKey) ?>" <?= $isLocked ? 'checked' : '' ?>>
                                <?= htmlspecialchars($values['label']) ?>
                            </label>
                            <button type="button" class="locked-field-toggle" aria-expanded="<?= $isLocked ? 'true' : 'false' ?>">
                                <span class="screen-reader-text"><?= i::__('Configurar validade') ?></span>
                            </button>
                        </div>
                        <div class="locked-field-body" <?= $isLocked ? '' : 'hidden' ?>>
                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="has-expiry" <?= $hasExpiry ? 'checked' : '' ?>>
                                <?= i::__('Este campo tem prazo de validade?') ?>
                            </label>

                            <div class="expiry-inputs" <?= $hasExpiry ? '' : 'hidden' ?>>
                                <label>
                                    <?= i::__('Duração') ?>:
                                    <input type="number" class="period-value" min="1" value="<?= htmlspecialchars((string)$periodValue) ?>">
                                </label>
                                <select class="period-unit">
                                    <option value="day" <?= $periodUnit === 'day' ? 'selected' : '' ?><?= i::__('Dia(s)') ?></option>
                                    <option value="month" <?= $periodUnit === 'month' ? 'selected' : '' ?><?= i::__('Mês(es)') ?></option>
                                    <option value="year" <?= $periodUnit === 'year' ? 'selected' : '' ?><?= i::__('Ano(s)') ?></option>
                                </select>
                            </div>

                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="is-invalidator" <?= $isInvalidator ? 'checked' : '' ?> <?= $hasExpiry ? '' : 'disabled' ?>>
                                <?= i::__('Este campo é invalidador?') ?>
                            </label>
                            <p class="registration-help invalidator-help"><?= i::__('Se um campo invalidador expirar, o selo inteiro será considerado inválido, mesmo que outros campos estejam dentro do prazo.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <h3><?= i::__('Taxonomias') ?></h3>
                <?php foreach ($space_taxonomies as $slug => $def) : ?>
                    <?php
                        $fieldKey = "space.terms:{$slug}";
                        $isLocked = in_array($fieldKey, $entity->lockedFields);
                        $config = $lockedFieldsConfig[$fieldKey] ?? [];
                        $hasExpiry = !empty($config['hasExpiry']);
                        $periodValue = $config['periodValue'] ?? '';
                        $periodUnit = $config['periodUnit'] ?? 'month';
                        $isInvalidator = !empty($config['isInvalidator']);
                    ?>
                    <div class="locked-field-card" data-field="<?= htmlspecialchars($fieldKey) ?>">
                        <div class="locked-field-header">
                            <label>
                                <input type="checkbox" name="lockedFields[]" value="<?= htmlspecialchars($fieldKey) ?>" <?= $isLocked ? 'checked' : '' ?>>
                                <?= htmlspecialchars($def->description ?: $slug) ?>
                            </label>
                            <button type="button" class="locked-field-toggle" aria-expanded="<?= $isLocked ? 'true' : 'false' ?>">
                                <span class="screen-reader-text"><?= i::__('Configurar validade') ?></span>
                            </button>
                        </div>
                        <div class="locked-field-body" <?= $isLocked ? '' : 'hidden' ?>>
                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="has-expiry" <?= $hasExpiry ? 'checked' : '' ?>>
                                <?= i::__('Este campo tem prazo de validade?') ?>
                            </label>

                            <div class="expiry-inputs" <?= $hasExpiry ? '' : 'hidden' ?>>
                                <label>
                                    <?= i::__('Duração') ?>:
                                    <input type="number" class="period-value" min="1" value="<?= htmlspecialchars((string)$periodValue) ?>">
                                </label>
                                <select class="period-unit">
                                    <option value="day" <?= $periodUnit === 'day' ? 'selected' : '' ?><?= i::__('Dia(s)') ?></option>
                                    <option value="month" <?= $periodUnit === 'month' ? 'selected' : '' ?><?= i::__('Mês(es)') ?></option>
                                    <option value="year" <?= $periodUnit === 'year' ? 'selected' : '' ?><?= i::__('Ano(s)') ?></option>
                                </select>
                            </div>

                            <label class="locked-field-checkbox">
                                <input type="checkbox" class="is-invalidator" <?= $isInvalidator ? 'checked' : '' ?> <?= $hasExpiry ? '' : 'disabled' ?>>
                                <?= i::__('Este campo é invalidador?') ?>
                            </label>
                            <p class="registration-help invalidator-help"><?= i::__('Se um campo invalidador expirar, o selo inteiro será considerado inválido, mesmo que outros campos estejam dentro do prazo.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>
