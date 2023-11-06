<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>
<div class="opportunity-evaluations-list" v-if="showList()">
    <div :class="['opportunity-evaluations-list__container', 'isOpen']">
        <button class="act-button" @click="toggleMenu()">
            <label class="label">{{textButton }}</label>
        </button>

        <div class="find">
            <div class="content">
                <input type="text" v-model="keywords">
                
                <button class="button-filter button--primary" @click="filterKeywordExec()">
                    <mc-icon name="filter"></mc-icon>
                    <label class="button-label"><?= i::__('Filtrar') ?></label>
                </button>
            </div>
            <div class="label-evaluation">
                <div class="label-evaluation__check">
                    <input type="checkbox" v-model="pending" class="label-evaluation__check--pending">
                    <span class="label-evaluation__check--label"><?= i::__('Mostrar somente pendentes') ?></span>
                </div>
            </div>
        </div>
        <ul class="evaluation-list">
            <li v-for="evaluation in evaluations" :class="[{'evaluation-list__card--modify': entity.id == evaluation.registrationid}, 'evaluation-list__card']">
                <div class="evaluation-list__content">
                    <a :href="evaluation.url" class="link">
                        <div class="card-header">
                            <mc-icon name='agent-1'></mc-icon>
                            <span class="card-header__name">{{evaluation.agentname}}</span>
                        </div>
                        <div class="card-content">
                            <div class=" card-content__middle">
                                <span class="subscribe"><?= i::__('Inscrição') ?></span>
                                <span class="value">
                                    <strong>{{evaluation.registrationid}}</strong>
                                </span>
                            </div>
                            <div class="card-content__middle">
                                <span class="subscribe"><?= i::__('Data da inscrição') ?></span>
                                <span class="value">
                                    <strong>{{dateFormat(entity.createTimestamp)}}</strong>
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
                            <mc-link route="registration/evaluation/" :params="[evaluation.registrationid]"  class="button button--primary-outline"><?= i::__('Acessar') ?></mc-link>

                        </div>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>