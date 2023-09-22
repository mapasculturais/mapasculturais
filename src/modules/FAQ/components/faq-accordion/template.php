<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tag-list
    mc-title
');
?>
<div class="faq-accordion" id="accordionRegister">
    <div v-for="section in data" class="faq-accordion__item">
        <template v-if="section.contexts.length>0" v-for="context in section.contexts">
           <div class="faq-accordion__contexts">
               <mc-title tag="h2" v-html="context.title" class="bold primary__color"></mc-title>
               <div v-html="context.description" class="faq-accordion__context semibold"></div>
            </div>
    
            <div v-for="question in context.questions" class="faq-accordion__items">
                <header  @click="toggle()" class="faq-accordion__header">
                    <mc-title tag="h3" class="bold faq-accordion__subtitle">{{question.question}}</mc-title>
                    <mc-icon :name="status ? 'arrowPoint-up':'arrowPoint-down'" class="primary__color"></mc-icon>
                </header>
                <div  v-if="status" class="faq-accordion__content">
                    <div class="far-accordion__list">
                        <div v-html="question.answer" class="faq-accordion__response"></div>
                        <mc-tag-list :tags="question.tags" classes="faq-accordion__tags"></mc-tag-list>
                    </div>
                </div>
            </div>
    </div>
    </template>

</div>