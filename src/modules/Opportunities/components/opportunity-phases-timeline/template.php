<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
');
?>
<section :class="['timeline', {'center': center}, {'big': big}]">
    <div v-for="item in phases" :class="['item', {'active': isActive(item)}, {'happened': itHappened(item)}]" :set="registration = getRegistration(item)">
        <div class="item__dot"> <span class="dot"></span> </div>

        <div class="item__content">
            <?php $this->applyComponentHook('item', 'begin'); ?>

            <div v-if="item.isFirstPhase" class="item__content--title"> <?= i::__('Fase de inscrições') ?> </div>
            <div v-if="!item.isFirstPhase" class="item__content--title"> {{item.name}} </div>

            <div v-if="!item.isLastPhase" class="item__content--description">
                <?= i::__('de') ?> <span v-if="dateFrom(item)">{{dateFrom(item)}}</span>
                <?= i::__('a') ?> <span v-if="dateTo(item)">{{dateTo(item)}}</span>
                <?= i::__('às') ?> <span v-if="hour(item)">{{hour(item)}}</span>
            </div>

            <div v-if="item.isLastPhase" class="item__content--description">
                <span v-if="item.publishTimestamp">
                    {{item.publishTimestamp.date('2-digit year')}}
                    <?= i::__('às') ?> 
                    {{item.publishTimestamp.time()}}
                </span>
            </div>

            <template v-if="registration">
                <?php $this->applyComponentHook('registration', 'begin'); ?>
                <div v-if="shouldShowResults(item)">
                    <p v-if="registration.status == 10"><?= i::__('Inscrição selecionada') ?></p>
                    <p v-if="registration.status == 8"><?= i::__('Inscrição suplente') ?></p>
                    <p v-if="registration.status == 3"><?= i::__('Inscrição não selecionada') ?></p>
                    <p v-if="registration.status == 2"><?= i::__('Inscrição inválida') ?></p>
                    <p v-if="registration.status == 0"><?= i::__('Inscrição não enviada') ?></p>
                </div>

                <div v-if="isDataCollectionPhase(item) && isActive(item)">
                    <mc-link :entity="registration" route="edit" class="button button--primary"><?= i::__('Preencher formulário') ?></mc-link>
                </div>
                <?php $this->applyComponentHook('registration', 'end'); ?>
            </template>
            <?php $this->applyComponentHook('item', 'end'); ?>
        </div>
    </div>
</section>