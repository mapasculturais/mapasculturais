<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

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
