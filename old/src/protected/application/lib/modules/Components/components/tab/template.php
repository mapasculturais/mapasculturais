<?php
$this->import('tabs');
?>
<section v-if="isActive || cached" v-show="isActive" :aria-hidden="!isActive" class="tab-component" :class="slug" role="tabpanel">
    <slot></slot>
</section>