<?php

use MapasCulturais\i;

$this->import('
    mc-entities
    mc-icon
    personal-access-token--modal
    personal-access-token--card
');
?>
<div class="pat-list">
    <div class="pat-list__header">
        <h3><?php i::_e('Tokens de Acesso Pessoal') ?></h3>
        <personal-access-token--modal @create="$refs.entities.refresh()"></personal-access-token--modal>
    </div>

    <mc-entities ref="entities" type="personal-access-token" :query="{}" select="id,name,permissions,tokenPrefix,lastUsedAt,expiresAt,createTimestamp,status">
        <template #default="{entities}">
            <div class="pat-list__items">
                <personal-access-token--card
                    v-for="token in entities"
                    :key="token.id"
                    :entity="token"
                    @revoked="entities.refresh()"
                ></personal-access-token--card>
            </div>
        </template>

        <template #empty>
            <div class="pat-list__empty">
                <?php i::_e('Nenhum token de acesso criado.') ?>
            </div>
        </template>
    </mc-entities>
</div>
