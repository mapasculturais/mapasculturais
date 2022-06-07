<div class="popover">
    <slot name="btn" :open="open" :close="close" :toggle="toggle" />
    
    <div v-if="active" class="popover__content" :class="openside">
        <slot name="content" :open="open" :close="close" :toggle="toggle" />
    </div>
</div>