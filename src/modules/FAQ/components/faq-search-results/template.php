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

<h3 class="bold faq-accordion__results"><?= i::__('Resultados da pesquisa') ?> '{{global.faqSearch}}'</h3>
<template v-if="results" v-for="section in results" class="faq-accordion">
    <div v-if="index" class="faq-accordion__contexts">
        <h2 v-html="section.title" class="bold primary__color">></h2>
        <div v-html="section.description" class="faq-accordion__context semibold"></div>
    </div>
    <template v-if="section.contexts.length > 0" v-for="context in section.contexts">
        <div class="faq-accordion__contexts">
            <mc-title tag="h2" v-html="context.title" class="bold primary__color"></mc-title>
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
<div v-if="results==0" class="faq-accordion__noresults">
    <img class="faq-accordion__image" src="<?php $this->asset('/img/unknown.png', true, true) ?>">
    <label class="faq-accordion__msg bold"><?= i::__('Ops! nenhum resultado foi encontrado para sua busca') ?></label>
    <a class="nounderline" href="<?= $this->controller->createUrl('index'); ?>"><label class="bold primary__color faq-accordion__backhelp"><?= i::__('VOLTAR À AJUDA') ?></label></a>
</div>