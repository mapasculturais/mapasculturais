<div class="popover">
    <slot name="btn"></slot>
    <button @click="popover($event)" :class="classbnt" > {{label}} </button>
    <div class="popover__content" id="popover" :class="openside">
        <slot></slot>
    </div>
</div>