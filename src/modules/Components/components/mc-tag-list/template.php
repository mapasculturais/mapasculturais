<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="mc-tag-list">
  <ul class="mc-tag-list__tagList">
    <li v-for="tag in tags" class="mc-tag-list__tag" :class="[classes, {'mc-tag-list__tag--editable': editable}]">
      <span>{{ this.labels ? this.labels[tag] : tag }}</span>
      <mc-icon v-if="editable" name="delete" @click="remove(tag)" is-link></mc-icon>
    </li>
  </ul>
</div>
