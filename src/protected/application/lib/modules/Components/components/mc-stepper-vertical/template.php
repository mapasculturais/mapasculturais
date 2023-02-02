<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-icon')
?>
<ol class="mc-stepper-vertical">
    <li v-for="(step, index) in steps" :class="{active: step.active}">
        <header>
            <slot name="header" :index="index" :step="step" :item="step.item">
                <slot name="header-title" :index="index" :step="step" :item="step.item">{{step.item.name || step.item.title || step.item.label}}</slot>
                <slot name="header-actions" :index="index" :step="step" :item="step.item">
                    <a v-if="step.active" @click="step.close()"><?= i::__('fechar') ?> <mc-icon name="arrowPoint-up"></mc-icon></a>
                    <a v-if="!step.active" @click="step.open()"><?= i::__('expandir') ?> <mc-icon name="arrowPoint-down"></mc-icon></a>
                </slot>
            </slot>
        </header> 
        <main v-if="step.active">
            <slot :index="index" :step="step" :item="step.item">
                o slot <strong><code>#default</code></strong> é obrigatório para o componente <strong><code>mc-stepper-vertical</code></strong>
            </slot>
        </main>
    </li>
</ol>