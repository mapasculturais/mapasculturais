<?php

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>
<div class="pat-card" :class="{ 'pat-card--revoked': entity.status < 0 }">
    <div class="pat-card__header">
        <div class="pat-card__info">
            <strong>{{ entity.name }}</strong>
            <span class="pat-card__mask">{{ entity.tokenPrefix }}****************</span>
        </div>
        <div class="pat-card__actions">
            <button v-if="entity.status > 0" @click="revoke()" class="button button--text button--text-del" :disabled="revoking">
                <mc-icon name="trash"></mc-icon>
                {{ revoking ? __('revogando', 'personal-access-token--card') : __('revogar', 'personal-access-token--card') }}
            </button>
            <span v-else class="pat-card__status pat-card__status--revoked">
                <mc-icon name="x"></mc-icon> <?php i::_e('Revogado') ?>
            </span>
        </div>
    </div>
    <div class="pat-card__meta">
        <span v-if="entity.lastUsedAt">
            <?php i::_e('Último uso') ?>: {{ entity.lastUsedAt.date('short') }}
        </span>
        <span v-if="entity.expiresAt">
            <?php i::_e('Expira em') ?>: {{ entity.expiresAt.date('short') }}
            <span v-if="isExpired" class="pat-card__expired"><?php i::_e('(expirado)') ?></span>
        </span>
        <span>
            <?php i::_e('Criado em') ?>: {{ entity.createTimestamp.date('short') }}
        </span>
    </div>
    <div class="pat-card__permissions" v-if="entity.permissions && entity.permissions.length">
        <span class="pat-perm" v-for="perm in entity.permissions" :key="perm">{{ perm }}</span>
    </div>
</div>
