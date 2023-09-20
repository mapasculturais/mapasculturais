<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="faq__accordion" id="accordionRegister">
    <div v-for="section in data" class="faq-accordion__item">
        <template v-if="section.contexts.length>0" v-for="context in section.contexts">
            {{context.title}}
            {{context.description}}
            <div v-for="question in context.questions" class="faq-accordion__items">

                <header @click="toggle()" class="faq-accordion__header">
                    <h5 class="bold">
                        {{question.question}}
                    </h5>
                    <mc-icon :name="status ? 'arrowPoint-up':'arrowPoint-down'" class="primary__color"></mc-icon>
                </header>
                <div v-if="status" class="faq-accordion__content">
                    <div class="accordion-body">
                        {{question.answer}}
                        <br>
                        {{question.tags.join(', ')}}
                    </div>
                </div>
            </div>
        </template>

    </div>
</div>