<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-avatar
    mc-icon
    select-entity
');
?>
<div v-if="entity != owner" class="entity-owner" :class="classes">
    <h4 class="bold">{{title}}</h4>
    <a class="entity-owner__owner" :href="owner.singleUrl" :title="owner.shortDescription">
        <div class="entity-owner__owner--img">
            <mc-avatar :entity="owner" size="xsmall"></mc-avatar>
        </div>
        <div class="entity-owner__owner--name">
            {{owner.name}}
        </div>
    </a>

    <div v-if="editable" class="entity-owner__edit">
        <mc-alert v-if="hasRequest" type="warning" class="entity-owner-pending">
            <div>
                <?= i::__('Aguardando aprovação de') ?> <strong>{{destinationName}}</strong>
            </div>
        </mc-alert>
        <select-entity v-if="!hasRequest" :query="query" type="agent" @select="changeOwner($event)" permissions="" openside="up-right">
            <template #button="{ toggle }">
                <a class="entity-owner__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                    <mc-icon name="exchange"></mc-icon>
                    <h4><?php i::_e('Alterar Propriedade') ?></h4>
                </a>
            </template>
        </select-entity>
    </div>

</div>