<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import("
    mc-icon
    mc-popover
    mc-tag-list
    mc-title
");
?>

<mc-popover classes="v-popper__popper--no-border" title="Informações">
    <div class="faq-info scrollbar">
        <mc-title class="faq-info__title bold">{{title || question.question}}</mc-title>
        <div class="faq-info__content" v-html="answer" ></div>
        <mc-tag-list class="faq-info__tags" :tags="tags"></mc-tag-list>
    </div>

    <template #button="popover">
        <span class="faq-info__button" @click.prevent="popover.toggle()" :title="title">
            <mc-icon name="help"></mc-icon>
        </span>
    </template>
</mc-popover>