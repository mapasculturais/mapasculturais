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
<div class="mc-side-menu" v-if="showList()">
    <div :class="['mc-side-menu__container', 'isOpen']">
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
                    <label class="label-evaluation__check--label"><?= i::__('Mostrar somente pendentes') ?></label>
                </div>
            </div>
        </div>
        <ul class="evaluation-list">
            <li v-for="evaluation in evaluations" :class="[{'evaluation-list__card--modify': entity.id == evaluation.registrationid}, 'evaluation-list__card']">
                <div class="evaluation-list__content">
                    <a :href="evaluation.url">
                        <div class="card-header">
                            <mc-icon name='agent-1'></mc-icon>
                            <label class="card-header__name">{{evaluation.agentname}}</label>
                        </div>
                        <div class="card-content">
                            <div class=" card-content__middle">
                                <label class="subscribe"><?= i::__('Inscrição') ?></label>
                                <span class="value">
                                    <strong>{{evaluation.registrationid}}</strong>
                                </span>
                            </div>
                            <div class="card-content__middle">
                                <label class="subscribe"><?= i::__('Data da inscrição') ?></label>
                                <span class="value">
                                    <strong>{{dateFormat(entity.createTimestamp)}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="card-state">
                            <label class="state"><?= i::__('Status de avaliação') ?></label>
                            <span :class="verifyState(evaluation)" class="card-state__info">
                                <mc-icon  name="circle"></mc-icon>
                                <h5 class="bold" v-if="evaluation.resultString">{{evaluation.resultString}}</h5>
                                <h5 class="bold" v-if="!evaluation.resultString"> <?= i::__('Pendente') ?></h5>
                            </span>
                        </div>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>