<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-loading
');
?>
<div class="opportunity-evaluations-list" v-if="showList()">
    <div :class="['opportunity-evaluations-list__container', 'isOpen']">
        <button class="act-button" @click="toggleMenu()">
            <label class="label">{{textButton }}</label>
        </button>

        <div class="find">
            <div class="content">
                <input type="text" v-model="keywords" @input="timeOutFind()" @keyup.enter="timeOutFind(0)" class="label-evaluation__search">
            </div>
            <div v-if="!pending" class="label-evaluation">
                <div class="label-evaluation__check">
                    <div class="field">
                        <label class="label-evaluation__check--label">
                            <?= i::__('Selecione para filtrar') ?>
                        </label>
                        <select v-model="filterStatus">
                            <template v-for="option in filtersOptions">
                                <option :value="option.value">{{option.label}}</option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
            <div v-if="evaluations.length > 0" class="count">
                <?= i::__('Total') ?> {{evaluations.length}} <?= i::__('Avaliações') ?>
            </div>
        </div>
        <mc-loading :condition="loading"><?= i::__('carregando...') ?></mc-loading>
        <ul v-if="!loading" class="evaluation-list scrollbar">
            <li v-if="evaluations.length <= 0" class="no-records">
                <?= i::__('Não foram encontrados registros') ?>
            </li>
            <li v-if="evaluations.length > 0" v-for="evaluation in evaluations" :key="evaluation.registrationId" :class="[{'evaluation-list__card--modify': entity.id == evaluation.registrationid}, 'evaluation-list__card']">
                <div :class="'evaluation-list__content '+colorByStatus(evaluation)">
                    <a :href="evaluation.url" class="link">
                        <div class="card-header">
                            <span class="card-header__name">{{evaluation.registrationNumber}}</span>
                        </div>

                        <div class="owner-entity">
                            <div class="owner" v-if="evaluation.agentsData?.['owner']?.name != ''">
                                <span>
                                    <small class="bold"><?= i::__('Agente responsável') ?></small>
                                </span>
                                <span>
                                    <small>{{evaluation.agentsData?.['owner']?.name}}</small>
                                </span>
                            </div>

                            <div class="coletive" v-if="evaluation.agentsData?.['coletivo']?.name">
                            <span>
                                <small class="bold"><?= i::__('Agente coletivo') ?></small>
                            </span>
                            <span>
                                <small>{{evaluation.agentsData?.['coletivo']?.name}}</small>
                            </span>
                            </div>
                        </div>

                        <div class="card-content">
                            <div class="card-content__middle">
                                <span class="subscribe"><?= i::__('Data da inscrição') ?></span>
                                <span v-if="evaluation.registrationSentTimestamp" class="value">
                                    <strong>{{evaluation.registrationSentTimestamp.date()}} {{evaluation.registrationSentTimestamp.time()}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="card-state">
                            <span class="state"><?= i::__('Resultado de avaliação') ?></span>
                            <span :class="verifyState(evaluation)" class="card-state__info">
                                <mc-icon  name="circle"></mc-icon>
                                <h5 class="bold" v-if="evaluation.resultString">{{evaluation.resultString}}</h5>
                                <h5 class="bold" v-if="!evaluation.resultString"> <?= i::__('Pendente') ?></h5>
                            </span>
                            <mc-link route="registration/evaluation/" :params="{id:evaluation.registrationId,user:userEvaluatorId}" icon="arrowPoint-right" right-icon class="button button--primary-outline"><?= i::__('Acessar') ?></mc-link>
                        </div>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>