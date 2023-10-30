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
    mc-title
');
?>

<mc-tabs> <!-- :class="{'hasDrafts': this.totalDrafts>0}" @changed="changed($event)" -->

    <?php $this->applyComponentHook('tabs', 'begin'); ?>
    <mc-tab label="<?= i::_e('Avaliações abertas e disponíveis') ?>" slug="openEvaluations" class="panel-evaluations">
        <?php $this->applyComponentHook('tabs-openEvaluations', 'begin'); ?>

        <form @submit="$event.preventDefault();" class="panel-evaluations__filter form"> <!-- @submit="entities.refresh(); $event.preventDefault();" -->
            <?php $this->applyComponentHook('tabs-openEvaluations-filter', 'begin'); ?>
            
            <div class="search">
                <input type="text" class="input" /> <!-- v-model="entities.query['@keyword']" @input="entities.refresh();" -->
                <button class="button button--icon">
                    <mc-icon name="search"></mc-icon>
                </button>
            </div>

            <select class="order primary__border--solid"> <!-- v-model="query['@order']" @change="entities.refresh();" -->
                <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
            </select>

            <?php $this->applyComponentHook('tabs-openEvaluations-filter', 'end'); ?>
        </form>

        <div class="panel-evaluations__cards">                   
            <?php $this->applyComponentHook('tabs-openEvaluations-cards', 'begin'); ?>
            <evaluation-card></evaluation-card>
            <?php $this->applyComponentHook('tabs-openEvaluations-cards', 'end'); ?>
        </div>  

        <?php $this->applyComponentHook('tabs-openEvaluations', 'end'); ?>
    </mc-tab>
    
    <mc-tab label="<?= i::_e('Avaliações encerradas') ?>" slug="closedEvaluations" class="panel-evaluations">
        <form @submit="$event.preventDefault();" class="panel-evaluations__filter"> <!-- @submit="entities.refresh(); $event.preventDefault();" -->
            <?php $this->applyComponentHook('tabs-closedEvaluations-filter', 'begin'); ?>

            <div class="search">
                <input type="text" class="input" /> <!-- v-model="entities.query['@keyword']" @input="entities.refresh();" -->
                <button class="button button--icon"> 
                    <mc-icon name="search"></mc-icon>
                </button>
            </div>

            <select class="order primary__border--solid"> <!-- v-model="query['@order']" @change="entities.refresh();" -->
                <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
            </select>

            <?php $this->applyComponentHook('tabs-closedEvaluations-filter', 'end'); ?>
        </form>

        <!-- Reduzir divs -->
        <div class="panel-evaluations__cards">                 
            <?php $this->applyComponentHook('tabs-closedEvaluations-cards', 'begin'); ?>
            <evaluation-card></evaluation-card>
            <?php $this->applyComponentHook('tabs-closedEvaluations-cards', 'end'); ?>
            <!-- <?= i::__('Você ainda não tem avaliações encerradas.') ?> -->
        </div>
    </mc-tab>
    <?php $this->applyComponentHook('tabs', 'end'); ?>

</mc-tabs>