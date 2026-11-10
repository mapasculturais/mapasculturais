<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
    mc-icon
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-seals-list', 'before'); ?>

<div :class="classes" class="entity-seals-list">
    <?php $this->applyTemplateHook('entity-seals-list', 'begin'); ?>

    <div v-if="editable" class="entity-seals-list__actions">
        <select-entity type="seal" @select="addSeal($event)" :query="query" openside="down-right">
            <template #button="{ toggle }">
                <button type="button" class="button button--primary button--icon button--sm" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionar selo') ?>
                </button>
            </template>
        </select-entity>
    </div>

    <div class="entity-seals-list__body">
        <p v-if="!hasSeals" class="entity-seals-list__empty">{{ emptyMessage }}</p>

        <ul v-else class="entity-seals-list__items">
            <li
                v-for="seal in sortedSeals"
                :key="seal.sealRelationId || seal.sealId"
                class="entity-seals-list__item"
                :class="{ 'entity-seals-list__item--expired': isExpired(seal) }">

                <div class="entity-seals-list__avatar">
                    <mc-avatar :entity="seal" size="medium"></mc-avatar>
                </div>

                <div class="entity-seals-list__content">
                    <div class="entity-seals-list__header">
                        <a class="entity-seals-list__name" :href="sealHref(seal)" :title="seal.name">
                            {{ seal.name }}
                        </a>
                        <span v-if="isExpired(seal)" class="entity-seals-list__expired">
                            <mc-icon name="exclamation"></mc-icon>
                            <?php i::_e('selo expirado') ?>
                        </span>
                        <div v-if="editable" class="entity-seals-list__remove">
                            <mc-confirm-button @confirm="removeSeal(seal)">
                                <template #button="modal">
                                    <button type="button" class="button button--text button--icon button--sm entity-seals-list__remove-btn" @click="modal.open()" title="<?php i::esc_attr_e('Remover selo') ?>">
                                        <mc-icon name="trash"></mc-icon>
                                    </button>
                                </template>
                                <template #message="message">
                                    {{ removeConfirmMessage(seal) }}
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>

                    <p v-if="seal.creator?.name" class="entity-seals-list__meta">
                        <span class="entity-seals-list__meta-label"><?php i::_e('Criador do selo:') ?></span>
                        <a v-if="seal.creator.singleUrl" class="entity-seals-list__meta-link" :href="seal.creator.singleUrl">{{ seal.creator.name }}</a>
                        <span v-else>{{ seal.creator.name }}</span>
                    </p>

                    <p v-if="seal.attributedBy?.name" class="entity-seals-list__meta">
                        <span class="entity-seals-list__meta-label"><?php i::_e('Selo atribuído por:') ?></span>
                        <a v-if="seal.attributedBy.singleUrl" class="entity-seals-list__meta-link" :href="seal.attributedBy.singleUrl">{{ seal.attributedBy.name }}</a>
                        <span v-else>{{ seal.attributedBy.name }}</span>
                    </p>

                    <p v-if="seal.shortDescription" class="entity-seals-list__description">
                        <span class="entity-seals-list__meta-label"><?php i::_e('Descrição curta:') ?></span>
                        {{ seal.shortDescription }}
                    </p>

                    <div class="entity-seals-list__dates">
                        <p class="entity-seals-list__date">
                            <mc-icon name="date"></mc-icon>
                            <span>
                                <span class="entity-seals-list__meta-label"><?php i::_e('Data de recebimento do selo:') ?></span>
                                {{ formatDate(seal.createTimestamp) || '00/00/0000' }}
                            </span>
                        </p>
                        <p class="entity-seals-list__date">
                            <mc-icon name="date"></mc-icon>
                            <span>
                                <span class="entity-seals-list__meta-label"><?php i::_e('Validade do selo:') ?></span>
                                {{ formatValidity(seal) }}
                            </span>
                        </p>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <?php $this->applyTemplateHook('entity-seals-list', 'end'); ?>
</div>

<?php $this->applyTemplateHook('entity-seals-list', 'after'); ?>
