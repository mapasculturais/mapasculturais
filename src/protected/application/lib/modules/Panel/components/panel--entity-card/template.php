<?php
use MapasCulturais\i;

$this->import('panel--entity-actions');
?>
<article class="panel__row entity-card">
    <header class="entity-card__header">
        <div>
            <div class="entity-card__picture">
                <slot name="picture" :entity="entity">
                    <img src="<?php $this->asset('img/default-image.svg')?>" alt="">
                </slot>
            </div>
            <h2 class="entity-card__title">
                <slot name="title" :entity="entity">
                    {{ entity?.name || entity?.email || entity?.number || entity?.id }}
                </slot>
            </h2>
        </div>
        <div>
            <div class="entity-card__header-actions">
                <slot name="header-actions" :entity="entity"></slot>
            </div>
        </div>
    </header>
    <main class="entity-card__main">
        <slot :entity="entity"></slot>
    </main>
    <footer class="entity-card__footer">
        <div class="entity-card__footer-actions">
            <slot name="footer-actions" :entity="entity">
                <panel--entity-actions :entity="entity"></panel--entity-actions>
            </slot>
        </div>
    </footer>
</article>
