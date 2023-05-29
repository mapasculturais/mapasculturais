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
<div v-if="showList()">
    <div :class="['mc-side-menu', {'isOpen': isOpen}]">
        <button class="mc-side-menu__button" @click="toggleMenu()">
            <label class="label">{{textButton }}</label>
            <div class="icon">
                <mc-icon v-if="!isOpen" name="arrow-right-ios"></mc-icon>
                <mc-icon v-if="isOpen" name="arrow-left-ios"></mc-icon>
            </div>
        </button>

        <template v-if="isOpen">
            <div class="find">
                <div class="content">

                    <div class="find-text">
                        <input type="text" v-model="keywords">
                        <div class="icon">
                            <mc-icon name="search"></mc-icon>
                        </div>
                    </div>
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
                <li v-for="evaluation in evaluations" class="evaluation-list__card">
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
                            <button class="button button-state">
                                <label v-if="evaluation.resultString">{{evaluation.resultString}}</label>
                                <label v-if="!evaluation.resultString"> <?= i::__('Pendente') ?></label>
                            </button>
                        </div>
                    </a>
                </li>
            </ul>
        </template>
    </div>
</div>