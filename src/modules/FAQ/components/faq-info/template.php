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

<mc-popover openside="down-right">
    <div class="faq-info">
        <mc-title class="faq-info__title bold">{{title || question.question}}</mc-title>
        <div class="faq-info__content" v-html="answer" ></div>
        <mc-tag-list class="faq-info__tags" :tags="tags"></mc-tag-list>
    </div>

    <template #button="popover">
        <a href="#" @click.prevent="popover.toggle()" :title="title"><mc-icon name="help"></mc-icon></a>
    </template>
</mc-popover>