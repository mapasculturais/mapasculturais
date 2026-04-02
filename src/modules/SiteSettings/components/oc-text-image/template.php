<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-dialog
')
?>

<div class="oc-text-image">
    <div class="sub-menu">
        <oc-tabs :entity="entity" :groups="tabGroups" initial-group="tabs" sotarege-ref="textImages">
            <template #text="{tab}">
                <slot :name="`${slug}-text`" :tab="tab" :entity="entity">
                    <?= i::__('Conteúdo da aba') ?> {{tab.label}} do {{slug}}
                </slot>
            </template>

            <template #image="{tab}">

                <slot :name="`${slug}-image`" :tab="tab" :entity="entity">
                    <?= i::__('Conteúdo da aba') ?> {{tab.label}} do {{slug}}
                </slot>
            </template>
        </oc-tabs>
    </div>

</div>