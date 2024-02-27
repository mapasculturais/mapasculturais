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
    <div style="max-width: 400px;">
        <mc-title>{{title || question.question}}</mc-title>
        <mc-tag-list :tags="tags"></mc-tag-list>
        <div v-html="answer" style="white-space: pre-line;"></div>
    </div>

    <template #button="popover">
        <a href="#" @click.prevent="popover.toggle()" :title="title"><mc-icon name="info-full"></mc-icon></a>
    </template>
</mc-popover>