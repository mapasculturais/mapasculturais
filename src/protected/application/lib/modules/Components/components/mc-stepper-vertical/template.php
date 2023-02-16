<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-icon')
?>
<div class="mc-stepper-vertical-wrapper">
    <ol class="mc-stepper-vertical">
        <template v-for="(step, index) in steps">
            <li :class="{active: step.active}">
                <section class="stepper-step">
                    <header class="stepper-header">
                        <slot class="stepper-header-title" name="header" :index="index" :step="step" :item="step.item">
                            <slot name="header-title" :index="index" :step="step" :item="step.item">{{step.item.name || step.item.title || step.item.label}}</slot>
                            <slot name="header-actions" :index="index" :step="step" :item="step.item">
                                <a class="expand-stepper" v-if="step.active" @click="step.close()"><label><?= i::__('Fechar') ?></label><mc-icon name="arrowPoint-up"></mc-icon></a>
                                <a class="expand-stepper" v-if="!step.active" @click="step.open()"><label><?= i::__('Expandir') ?></label> <mc-icon name="arrowPoint-down"></mc-icon></a>
                            </slot>
                        </slot>
                    </header>
                    <main v-if="step.active">
                        <slot :index="index" :step="step" :item="step.item">
                            o slot <strong><code>#default</code></strong> é obrigatório para o componente <strong><code>mc-stepper-vertical</code></strong>
                        </slot>
                    </main>
                </section>
            </li>
            <slot name="after-li" :step="step" :item="step.item"></slot>
        </template>
    </ol>
</div>