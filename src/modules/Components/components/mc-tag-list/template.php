<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="mc-tag-list">
  <ul class="mc-tag-list__list">
    <li v-for="tag in tags" class="mc-tag" :class="[itemClass, {'mc-tag--editable': editable}]">
      <span>{{ this.labels ? this.labels[tag] : tag }}</span>
      <mc-icon v-if="editable" name="delete" @click="remove(tag)" is-link></mc-icon>
    </li>
  </ul>
</div>
