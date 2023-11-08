<?php
/**
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    evaluation-card
    mc-loading
');
?>

<div v-if="!isAdmin" class="panel--entity-support">
    <div class="panel--entity-support__content">
        <h3 class="panel--entity-support__title bold"> <?php i::_e('Suporte disponível')?> </h3>

        <div class="panel--entity-support__content-cards">
            <carousel :settings="settings" class="carousel--panel">
                <slide v-for="opportunity in opportunities" :key="opportunity.id">
                    <evaluation-card :entity="opportunity" buttonLabel="<?= i::esc_attr__('Realizar suporte') ?>"></evaluation-card>
                </slide>

                <template #addons>
                    <div class="actions">
                        <navigation />
                    </div>
                </template>
            </carousel>
        </div>
    </div>
</div>