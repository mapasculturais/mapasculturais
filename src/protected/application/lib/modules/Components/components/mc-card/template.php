<component :is="tag" class="mc-card">
    <header v-if="hasSlot('title')" class="mc-card__title">
        <slot name="title"></slot>
    </header>
    <main class="mc-card__content">
        <slot></slot>
        <slot name="content"></slot>
    </main>
</component>