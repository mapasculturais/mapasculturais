<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-entities
    mc-icon
    mc-link
');
?>
<?php $this->applyTemplateHook('entity-connections-list', 'before'); ?>

<div :class="classes" class="entity-connections-list">
    <?php $this->applyTemplateHook('entity-connections-list', 'begin'); ?>

    <p v-if="!hasItems" class="entity-connections-list__empty">{{ resolvedEmptyMessage }}</p>

    <template v-else-if="type === 'opportunity'">
        <ul class="entity-connections-list__items">
            <li v-for="item in opportunities" :key="item.id" class="entity-connections-list__item">
                <div class="entity-connections-list__avatar">
                    <mc-avatar :entity="item" size="medium"></mc-avatar>
                </div>

                <div class="entity-connections-list__content">
                    <div class="entity-connections-list__header">
                        <mc-link :entity="item" class="entity-connections-list__name">{{ item.name }}</mc-link>
                        <span v-if="opportunityStatus(item)" class="entity-connections-list__badge">
                            {{ opportunityStatus(item) }}
                        </span>
                    </div>

                    <p v-if="item.type?.name" class="entity-connections-list__meta">
                        <span class="entity-connections-list__meta-label">{{ typeLabel }}</span>
                        {{ item.type.name }}
                    </p>

                    <p v-if="item.shortDescription" class="entity-connections-list__description">
                        {{ item.shortDescription }}
                    </p>

                    <p v-if="areas(item).length" class="entity-connections-list__meta">
                        <span class="entity-connections-list__meta-label">{{ areasLabel }}</span>
                        <span class="entity-connections-list__areas">{{ areasText(item) }}</span>
                    </p>

                    <div class="entity-connections-list__dates">
                        <p v-if="item.registrationFrom" class="entity-connections-list__date">
                            <mc-icon name="date"></mc-icon>
                            <span>
                                <span class="entity-connections-list__meta-label"><?php i::_e('Data de início da oportunidade:') ?></span>
                                {{ formatDate(item.registrationFrom) }}
                            </span>
                        </p>
                        <p v-if="item.registrationTo" class="entity-connections-list__date">
                            <mc-icon name="date"></mc-icon>
                            <span>
                                <span class="entity-connections-list__meta-label"><?php i::_e('Data de fim da oportunidade:') ?></span>
                                {{ formatDate(item.registrationTo) }}
                            </span>
                        </p>
                    </div>

                    <p v-if="item.owner?.name" class="entity-connections-list__meta entity-connections-list__responsible">
                        <mc-icon name="agent-1"></mc-icon>
                        <span>
                            <span class="entity-connections-list__meta-label"><?php i::_e('Responsável:') ?></span>
                            <a v-if="item.owner.singleUrl" class="entity-connections-list__meta-link" :href="item.owner.singleUrl">{{ item.owner.name }}</a>
                            <span v-else>{{ item.owner.name }}</span>
                        </span>
                    </p>
                </div>
            </li>
        </ul>
    </template>

    <mc-entities
        v-else
        :type="type"
        :select="select"
        order="name ASC"
        :ids="normalizedIds"
        #default="{entities}">
        <ul v-if="entities.length" class="entity-connections-list__items">
            <li v-for="item in entities" :key="item.id" class="entity-connections-list__item">
                <div class="entity-connections-list__avatar">
                    <mc-avatar :entity="item" size="medium"></mc-avatar>
                </div>

                <div class="entity-connections-list__content">
                    <div class="entity-connections-list__header">
                        <mc-link :entity="item" class="entity-connections-list__name">{{ item.name }}</mc-link>
                        <span v-if="roleLabel" class="entity-connections-list__badge">{{ roleLabel }}</span>
                    </div>

                    <p v-if="item.type?.name" class="entity-connections-list__meta">
                        <span class="entity-connections-list__meta-label">{{ typeLabel }}</span>
                        {{ item.type.name }}
                    </p>

                    <p v-if="type === 'project' && item.shortDescription" class="entity-connections-list__description">
                        {{ item.shortDescription }}
                    </p>

                    <p v-if="type === 'space' && item.endereco" class="entity-connections-list__meta entity-connections-list__location">
                        <mc-icon name="pin"></mc-icon>
                        <span>{{ item.endereco }}</span>
                    </p>

                    <p v-if="type === 'space' && item.acessibilidade === 'Sim'" class="entity-connections-list__meta entity-connections-list__accessibility">
                        <mc-icon name="wheelchair"></mc-icon>
                        <span><?php i::_e('Oferece acessibilidade') ?></span>
                    </p>

                    <p v-if="areas(item).length" class="entity-connections-list__meta">
                        <span class="entity-connections-list__meta-label">
                            {{ areasLabel }}{{ type !== 'opportunity' ? ' (' + areas(item).length + '):' : '' }}
                        </span>
                        <span class="entity-connections-list__areas">{{ areasText(item) }}</span>
                    </p>

                    <p v-if="formatDate(item.createTimestamp)" class="entity-connections-list__date">
                        <mc-icon name="date"></mc-icon>
                        <span>
                            <span class="entity-connections-list__meta-label">{{ sinceLabel }}</span>
                            {{ formatDate(item.createTimestamp) }}
                        </span>
                    </p>
                </div>
            </li>
        </ul>
        <p v-else class="entity-connections-list__empty">{{ resolvedEmptyMessage }}</p>
    </mc-entities>

    <?php $this->applyTemplateHook('entity-connections-list', 'end'); ?>
</div>

<?php $this->applyTemplateHook('entity-connections-list', 'after'); ?>
