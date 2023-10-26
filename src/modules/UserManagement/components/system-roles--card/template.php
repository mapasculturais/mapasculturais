<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
$this->import('
    mc-confirm-button
    panel--entity-card
    system-roles--modal
') 
?>
<panel--entity-card :entity="entity" class="system-roles-card" :on-delete-remove-from-lists="false" @deleted="$emit('deleted', $event)" @published="$emit('deleted', $event)">
    <div class="system-roles-card__info"><label class="system-roles-card__label">ID:</label><code class="system-roles-card__entityInfo">{{entity.id}}</code></div>
    <div class="system-roles-card__slug"><label class="system-roles-card__label">Slug:</label><code class="system-roles-card__entityInfo">{{entity.slug}}</code></div>
    <a class="system-roles-card__close" v-if="showItem" @click="toggle()"><label class="system-roles-card__permissions"><?= i::__('Permissões') ?></label><mc-icon name="arrowPoint-up"></mc-icon></a>
    <a class="system-roles-card__expand" v-if="!showItem"  @click="toggle()"><label class="system-roles-card__permissions"><?= i::__('Permissões') ?></label> <mc-icon name="arrowPoint-down"></mc-icon></a>
    <div class="system-roles-card__columns system-roles-card__content" v-if="showItem">
        <section v-for="grp in permissions" :key="grp.entity" class="system-roles-card__column"> 
            <h5 class="system-roles-card__users">{{text(grp.entity)}}</h5>
            <div class="system-roles-card__list">
                <ul>
                    <li v-for="perm in grp.actions" :key="perm.action" class="system-roles-card__item">{{perm.label}}</li>
                </ul>
            </div>
        </section>
    </div>
    
    <template #entity-actions-left>
        <mc-confirm-button
            :message="`<?= i::esc_attr__('Deseja excluir a função "${entity.name}"?') ?>`"
            @confirm="entity.delete(true)">
            <template #button="modal">
                <button @click="modal.open()" class="button button--text delete button--icon">
                    <?= i::__('Excluir') ?>
                    <mc-icon name='trash'></mc-icon>
                </button>
            </template>
            
        </mc-confirm-button>
    </template>

    <template #entity-actions-right >
        <system-roles--modal 
            v-if="entity.status == 1"
            :entity="entity">
        </system-roles--modal>
    </template>

</panel--entity-card>