<?php
use MapasCulturais\i;

$this->import('panel--entity-actions');
?>
<article class="panel__row panel-entity-card">
    <header class="panel-entity-card__header">
        <div>
            <div class="panel-entity-card__picture">
                <slot name="picture" :entity="entity">
                    <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarSmall?.url" alt="">
                    <img v-if="!entity.files.avatar" src="<?php $this->asset('img/default-image.svg')?>" alt="">
                </slot>
            </div>
            <h2 class="panel-entity-card__title">
                <slot name="title" :entity="entity">
                    {{ entity?.name || entity?.email || entity?.number || entity?.id }}
                </slot>
            </h2>
        </div>
        <div>
            <div class="panel-entity-card__header-actions">
                <slot name="header-actions" :entity="entity"></slot>
            </div>
        </div>
    </header>
    <main class="panel-entity-card__main">
        <slot :entity="entity"></slot>
    </main>
    <footer class="panel-entity-card__footer">
        <div class="panel-entity-card__footer-actions">
            <slot name="footer-actions" :entity="entity">
                <panel--entity-actions 
                    :entity="entity" 
                    @deleted="$emit('deleted', arguments)"
                    @archived="$emit('archived', arguments)"
                    @published="$emit('published', arguments)"
                />
            </slot>
        </div>
    </footer>
</article>
