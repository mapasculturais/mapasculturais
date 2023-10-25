<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
');
?>

<div class="evaluation-card">
    <div class="evaluation-card__content">
        <mc-avatar :entity="{'files': ''}" size="medium"></mc-avatar>

        <div class="evaluation-card__group">
            <mc-title tag="h3" size="big" class="evaluation-card__title">Nome da oportunidade<!-- nome oportunidade --></mc-title>
    
            <div class="evaluation-card__infos">
                <div class="evaluation-card__info"> 
                    <span><?= i::__('FASE') ?>: <strong>Fase de avaliação <!-- type --></strong></span>
                    <span><?= i::__('TIPO') ?>: <strong>Avaliação simplificada <!-- type --></strong></span>
                </div>
    
                <div class="evaluation-card__info">
                    <span>
                        <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: 
                        <strong>28/04/2023 até 30/06/2023 <!-- evaluationFrom --> <!-- <?= i::__('até') ?> --> <!-- evaluationTo - date --> <!-- <?= i::__('as') ?> --> <!-- evaluationTo - time --></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <button class="button button--primary button--right-icon evaluation-card__button">
        <?= i::__('Avaliar') ?>
        <mc-icon name="arrow-right-ios"></mc-icon>
    </button>
    <!-- <mc-link route="opportunity/userEvaluations" :params="[item.opportunity.id]" class="button button--primary evaluation-button"> <?= i::__('Avaliar') ?> <mc-icon name="arrow-right-ios"></mc-icon></mc-link> -->
</div>