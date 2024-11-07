<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-link
    mc-icon
    registration-form-timeline
    registration-status
');
?>
<section :class="['timeline', {'center': center}, {'big': big}]">
    <?php $this->applyComponentHook('item', 'before'); ?>
    <div v-for="item in phases" :class="['item', {'active': isActive(item)}, {'happened': itHappened(item)}]" :set="registration = getRegistration(item)">
        <div class="item__dot"> <span class="dot"></span> </div>
        <div class="item__content">
            <?php $this->applyComponentHook('item', 'begin'); ?>
            <div v-if="item.isFirstPhase" class="item__content--title"> <?= i::__('Fase de inscrições') ?> </div>
            <div v-if="!item.isFirstPhase" class="item__content--title"> {{item.name}} </div>
            <div v-if="!item.isLastPhase && (!phases[0].isContinuousFlow || (phases[0].isContinuousFlow && phases[0].hasEndDate))" class="item__content--description">
                <h5 class="semibold"><?= i::__('de') ?> <span v-if="dateFrom(item)">{{dateFrom(item)}}</span>
                <?= i::__('a') ?> <span v-if="dateTo(item)">{{dateTo(item)}}</span>
                <?= i::__('às') ?> <span v-if="hour(item)">{{hour(item)}}</span></h5>
            </div>
            <div v-if="item.isLastPhase" class="item__content--description">
                <span v-if="item.publishTimestamp">
                    {{item.publishTimestamp.date('2-digit year')}}
                    <?= i::__('às') ?>
                    {{item.publishTimestamp.time()}}
                </span>
            </div>
            
            <template v-if="registration && item.isFirstPhase">
                <registration-form-timeline :entity="registration"></registration-form-timeline>
            </template>

            <template v-if="registration">
                <?php $this->applyComponentHook('registration', 'begin'); ?>
                
                <registration-status v-if="shouldShowResults(item)" :registration="registration" :phase="item"></registration-status>

                <div v-if="isDataCollectionPhase(item) && isActive(item, registration) && registration.status == 0">
                    <mc-link :entity="registration" route="edit" class="button button--primary"><?= i::__('Preencher formulário') ?></mc-link>
                </div>
                <?php $this->applyComponentHook('registration', 'end'); ?>
            </template>
            <?php $this->applyComponentHook('item', 'end'); ?>
        </div>
    </div>
    <?php $this->applyComponentHook('item', 'after'); ?>
</section>