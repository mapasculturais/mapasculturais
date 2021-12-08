<section v-if="isActive || cached" v-show="isActive" :aria-hidden="! isActive" :class="panelClass" :id="computedId" role="tabpanel" ref="tab">
    <slot />
</section>