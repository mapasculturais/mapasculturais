<component :is="tag" class="mapas-card">
    <header v-if="hasSlot('title')" class="mapas-card__title">
        <slot name="title"></slot>
    </header>
    <main class="mapas-card__content">
        <slot></slot>
        <slot name="content"></slot>
    </main>
</component>