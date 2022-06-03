<div class="popover">
    <slot name="btn" :onClick="$event => popover($event)" />
    
    <div class="popover__content" id="popover" :class="openside">
        <slot name="content" />
    </div>
</div>