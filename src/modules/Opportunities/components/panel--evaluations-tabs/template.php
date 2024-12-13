<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    evaluation-card
    mc-entities
    mc-tab
    mc-tabs
');
?>

<mc-tabs>
    <?php $this->applyComponentHook('tabs', 'begin'); ?>
    <mc-tab label="<?= i::_e('Avaliações abertas e disponíveis') ?>" slug="openEvaluations" class="panel-evaluations">
        <?php $this->applyComponentHook('tabs-openEvaluations', 'begin'); ?>
        <mc-entities name="evaluationsList" type="opportunity" endpoint="find" :query="query" select="id,name,parent.{name,status},status,registrationFrom,registrationTo,isContinuousFlow,hasEndDate">
            <template #header="{entities}">
                <form class="panel-evaluations__filter form" @submit="entities.refresh(); $event.preventDefault();">
                    <?php $this->applyComponentHook('tabs-openEvaluations-filter', 'begin'); ?>
                    <div class="search">
                        <input type="text" v-model="entities.query['@keyword']" class="input" @input="entities.refresh();" placeholder="<?= i::esc_attr__("Pesquisar") ?>"/>
                        <button class="button button--icon">
                            <mc-icon name="search"></mc-icon>
                        </button>
                    </div>
                    <select class="order primary__border--solid" v-model="query['@order']" @change="entities.refresh();">
                        <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                        <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                        <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                    </select>
                    <?php $this->applyComponentHook('tabs-openEvaluations-filter', 'end'); ?>
                </form>
            </template>
            <template #default="{entities}">
                <div class="panel-evaluations__cards">                   
                    <?php $this->applyComponentHook('tabs-openEvaluations-cards', 'begin'); ?>      

                    <evaluation-card v-for="evaluation in entities" :entity="evaluation" buttonLabel="<?= i::esc_attr__('Avaliar') ?>"></evaluation-card>

                    <?php $this->applyComponentHook('tabs-openEvaluations-cards', 'end'); ?>
                </div>
            </template>
        </mc-entities>
        <?php $this->applyComponentHook('tabs-openEvaluations', 'end'); ?>
    </mc-tab>
    <?php $this->applyComponentHook('tabs', 'end'); ?>
</mc-tabs>