<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-accordion    
    mc-tag-list
    mc-title
');
?>
<template v-for="section in data" class="faq-accordion">
    <template v-if="section.contexts.length > 0" v-for="context in section.contexts">
        <div class="faq-accordion__contexts">
            <h3 v-html="context.title" class="bold primary__color"></h3>
            <div v-html="context.description" class="faq-accordion__context semibold"></div>
        </div>
        <div class="faq-accordion__items">
            <template v-for="question in context.questions">
                <mc-accordion>
                    <template #title>
                        {{question.question}}
                    </template>
                    <template #content>
                        <div class="far-accordion__list">
                            <div v-html="question.answer" class="faq-accordion__response"></div>
                            <mc-tag-list :tags="question.tags" classes="faq-accordion__tags"></mc-tag-list>
                        </div>
                    </template>
                </mc-accordion>
            </template>
        </div>
    </template>
</template>