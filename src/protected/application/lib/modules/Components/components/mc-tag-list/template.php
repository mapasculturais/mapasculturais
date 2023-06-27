<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="mc-tag-list">
  <ul class="mc-tag-list__tagList">
    <li v-for="tag in tags" :class="[classes, {'mc-tag-list__tagList--view': !editable, 'mc-tag-list__tagList--tag': editable}]">
      <span>{{ this.labels ? this.labels[tag] : tag }}</span>
      <mc-icon v-if="editable" name="delete" @click="remove(tag)"></mc-icon>
    </li>
  </ul>
</div>
