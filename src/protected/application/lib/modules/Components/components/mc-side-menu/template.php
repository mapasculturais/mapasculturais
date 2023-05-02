<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-icon');
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
            <div>
                <div>
                    <input type="text" v-model="keywords">
                </div>
                <div>
                    <label>
                        <input type="checkbox" v-model="pending">
                        <?= i::__('Mostrar somente pendentes') ?>
                    </label>
                </div>
            </div>
            <ul>
                <li v-for="evaluation in evaluations">

                    <div>
                        <mc-icon name='agent-1'></mc-icon>
                        <span>{{evaluation.agentname}}</span>
                    </div>
                    <div>
                        <div>
                            <label><?= i::__('Inscrição') ?></label>
                            <span>
                                <strong>{{evaluation.registrationid}}</strong>
                            </span>
                        </div>
                        <div>
                            <label><?= i::__('Data da inscrição') ?></label>
                            <span>
                                <strong>{{dateFormat(entity.createTimestamp)}}</strong>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label><?= i::__('Status da avaliaçõe') ?></label>
                        <span v-if="evaluation.resultString">{{evaluation.resultString}}</span>
                        <span v-if="!evaluation.resultString"> <?= i::__('Pendente') ?></span>
                    </div>
                    <div>
                        <a :href="evaluation.url" type="button"><?= i::__('Acessar') ?></a>
                    </div>
                </li>
            </ul>
        </template>
    </div>
</div>