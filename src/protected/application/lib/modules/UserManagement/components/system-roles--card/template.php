<?php 
use MapasCulturais\i;

$this->import('panel--entity-card') 
?>
<panel--entity-card :entity="entity">
    <code>ID {{entity.id}}</code>
    <code>slug: {{entity.slug}}</code>

    <div class="grid-12">
        <section v-for="grp in permissions" :key="grp.entity" class="col-4"> 
            <h4>{{text(grp.entity)}}</h4>
            <div>
                <ul>
                    <li v-for="perm in grp.actions" :key="perm.action">{{perm.label}}</li>
                </ul>
            </div>
        </section>
    </div>

    <template #footer-actions="{entity}">
        <confirm-button
            :message="`<?= i::esc_attr__('Deseja excluir a função "${entity.name}"?') ?>`"
            @confirm="entity.delete(true)">
            <template #button="modal">
                <button @click="modal.open()" class="button button--text delete button--icon">
                    <?= i::__('Excluir') ?>
                    <mc-icon name='trash'></mc-icon>
                </button>
            </template>
            
        </confirm-button>
        <system-roles--modal :entity="entity"></system-roles--modal>
    </template>
</panel--entity-card>