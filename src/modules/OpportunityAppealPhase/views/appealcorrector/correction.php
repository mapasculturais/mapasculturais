<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var int $assignmentId
 * @var OpportunityAppealPhase\Entities\RegistrationAppealReview $assignment
 */

use MapasCulturais\i;

$this->layout = 'panel';

$this->import('
    mc-icon
    opportunity-appeal-correction-evaluation
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon opportunity__background">
                    <mc-icon name="opportunity"></mc-icon>
                </div>

                <h2><?= i::__('Correção de avaliação (recurso)') ?></h2>
            </div>
        </div>

        <p class="panel-page__header-subtitle">
            <?= i::__('Consulte o formulário da inscrição, revise o parecer do recurso (se liberado) e informe a nota corrigida nos critérios liberados.') ?>
        </p>
    </header>

    <opportunity-appeal-correction-evaluation :assignment-id="<?= (int) $assignmentId ?>"></opportunity-appeal-correction-evaluation>
</div>
