<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    evaluation-card
    mc-tab
    mc-tabs
    mc-title
');
?>

<mc-tabs> <!-- :class="{'hasDrafts': this.totalDrafts>0}" @changed="changed($event)" -->

    <mc-tab label="<?= i::_e('Avaliações') ?>" slug="evaluations">
        <div class="panel-evaluations">
            <div class="panel-evaluations__filter">
                <form class="form" @submit="$event.preventDefault();"> <!-- @submit="entities.refresh(); $event.preventDefault();" -->
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
                </form>
            </div>

            <div class="panel-evaluations__evaluations">
                <mc-title tag="h4" :short-length="0" :long-length="100" size="medium" class="panel-evaluations__label bold">
                    <?= i::__('Avaliações abertas e disponíveis') ?>
                </mc-title>
                                 
                <div class="panel-evaluations__evaluations-cards">
                    <!-- <evaluation-card></evaluation-card> -->
                    <?= i::__('Você não tem avaliações disponíveis.') ?>
                </div>
            </div>   

            <div class="panel-evaluations__evaluations">
                <mc-title tag="h4" :short-length="0" :long-length="100" size="medium" class="panel-evaluations__label bold">
                    <?= i::__('Avaliações encerradas') ?>
                </mc-title>
                                 
                <div class="panel-evaluations__evaluations-cards">
                    <evaluation-card></evaluation-card>
                    <!-- <?= i::__('Você ainda não tem avaliações encerradas.') ?> -->
                </div>
            </div>  
        </div>
    </mc-tab>
    
    <mc-tab label="<?= i::_e('Pareceres') ?>" slug="opinions">
        <div class="panel-evaluations">
            <div class="panel-evaluations__filter">
                <form class="form" @submit="$event.preventDefault();"> <!-- @submit="entities.refresh(); $event.preventDefault();" -->
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
                </form>
            </div>

            <div class="panel-evaluations__evaluations">
                <mc-title tag="h4" :short-length="0" :long-length="100" size="medium" class="panel-evaluations__label bold">
                    <?= i::__('Pareceres abertas e disponíveis') ?>
                </mc-title>
                                 
                <div class="panel-evaluations__evaluations-cards">
                    <evaluation-card></evaluation-card>
                    <!-- <?= i::__('Você não tem pareceres disponíveis.') ?> -->
                </div>
            </div>   

            <div class="panel-evaluations__evaluations">
                <mc-title tag="h4" :short-length="0" :long-length="100" size="medium" class="panel-evaluations__label bold">
                    <?= i::__('Pareceres encerradas') ?>
                </mc-title>
                                 
                <div class="panel-evaluations__evaluations-cards">
                    <!-- <evaluation-card></evaluation-card> -->
                    <?= i::__('Você ainda não tem pareceres encerrados.') ?>
                </div>
            </div>
        </div>
    </mc-tab>
</mc-tabs>