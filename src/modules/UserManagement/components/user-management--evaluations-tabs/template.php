<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    evaluation-card
    mc-avatar
    mc-entities
    mc-icon
    mc-tab
    mc-tabs
    mc-title
    panel--entity-card
');

$tabs = [
    'started' => i::esc_attr__('Iniciada'),
    'completed' => i::esc_attr__('Concluída'),
    'sent' => i::esc_attr__('Enviada'),
];

$sort_options = [
    'updateTimestamp DESC' => i::__('Modificadas recentemente'),
    'updateTimestamp ASC' => i::__('Modificadas há mais tempo'),
    'createTimestamp DESC' => i::__('Mais recentes primeiro'),
    'createTimestamp ASC' => i::__('Mais antigas primeiro'),
    'sentTimestamp DESC' => i::__('Enviadas recentemente'),
    'sentTimestamp ASC' => i::__('Enviadas há mais tempo'),
];
?>
<mc-tabs class="entity-tabs" sync-hash>
    <mc-tab cache key="phases" label="<?= i::esc_attr__('Fase de avaliação') ?>" slug="phases" class="panel-evaluations">
        <mc-entities name="userEvaluableOpportunities"
            type="opportunity"
            endpoint="findEvaluable"
            :select="phasesSelect"
            :query="phasesQuery"
            watch-query>
            <template #header="{entities}">
                <form class="panel-evaluations__filter form entity-tabs__filters panel__row" @submit="entities.refresh(); $event.preventDefault();">
                    <div class="search">
                        <input type="text"
                            v-model="entities.query['@keyword']"
                            class="entity-tabs__search-input input"
                            @input="entities.refresh();"
                            placeholder="<?= i::esc_attr__('Pesquisar') ?>"/>
                        <button class="button button--icon">
                            <mc-icon name="search"></mc-icon>
                        </button>
                    </div>
                    <label> <?= i::__('Ordernar por:') ?>
                        <select class="entity-tabs__search-select primary__border--solid" v-model="phasesQuery['@order']" @change="entities.refresh();">
                            <option value="owner.name ASC"><?= i::__('ordem alfabética') ?></option>
                            <option value="createTimestamp DESC"><?= i::__('mais recentes primeiro') ?></option>
                            <option value="createTimestamp ASC"><?= i::__('mais antigas primeiro') ?></option>
                        </select>
                    </label>
                </form>
            </template>

            <template #default="{entities}">
                <div class="panel-evaluations__cards">
                    <evaluation-card
                        v-for="evaluation in entities"
                        :key="evaluation.__objectId"
                        :entity="evaluation"
                        button-label="<?= i::esc_attr__('Acessar') ?>">
                    </evaluation-card>
                </div>
            </template>
        </mc-entities>
    </mc-tab>

    <?php foreach ($tabs as $status => $label): ?>
    <mc-tab cache key="<?= $status ?>" label="<?= $label ?>" slug="<?= $status ?>">
        <mc-entities name="registrationevaluation:<?= $status ?>"
            type="registrationevaluation"
            :select="select"
            :query="queries['<?= $status ?>']"
            :limit="limit"
            :order="queries['<?= $status ?>']['@order']"
            watch-query>
            <template #header="{entities}">
                <form class="entity-tabs__filters panel__row" @submit="$event.preventDefault();">
                    <label> <?= i::__('Ordernar por:') ?>
                        <select class="entity-tabs__search-select primary__border--solid" v-model="queries['<?= $status ?>']['@order']">
                            <?php foreach ($sort_options as $value => $option_label): ?>
                                <option value="<?= htmlentities($value) ?>"><?= htmlentities($option_label) ?></option>
                            <?php endforeach ?>
                        </select>
                    </label>
                </form>
            </template>

            <template #default="{entities}">
                <panel--entity-card v-for="entity in entities" :key="entity.__objectId" :entity="entity" class="panel__row">
                    <template #picture>
                        <mc-avatar :entity="entity.registration?.opportunity" size="medium"></mc-avatar>
                    </template>

                    <template #title>
                        <a :href="evaluationUrl(entity)" class="panel-entity-card__header--info-link">
                            <mc-title tag="h2" :shortLength="100" :longLength="110">
                                {{ entity.registration?.opportunity?.name || entity.registration?.number || entity.id }}
                            </mc-title>
                        </a>
                    </template>

                    <template #subtitle>
                        <span v-if="entity.registration?.number">
                            <?= i::__('Inscrição') ?>: <strong>{{ entity.registration.number }}</strong>
                        </span>
                        <span v-if="entity.result">
                            — <?= i::__('Resultado') ?>: <strong>{{ entity.result }}</strong>
                        </span>
                        <span v-if="entity.committee">
                            — <?= i::__('Comissão') ?>: <strong>{{ entity.committee }}</strong>
                        </span>
                    </template>

                    <template #entity-actions-left>
                        &nbsp;
                    </template>

                    <template #entity-actions-center>
                        &nbsp;
                    </template>

                    <template #entity-actions-right>
                        <a :href="evaluationUrl(entity)" class="button button--primary-outline button--icon button-action">
                            <?php i::_e('Acessar') ?> <mc-icon name="arrowPoint-right"></mc-icon>
                        </a>
                    </template>
                </panel--entity-card>
            </template>
        </mc-entities>
    </mc-tab>
    <?php endforeach ?>
</mc-tabs>
