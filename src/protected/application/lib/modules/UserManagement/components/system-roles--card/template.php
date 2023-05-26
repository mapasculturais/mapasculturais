<?php 
use MapasCulturais\i;

$this->import('
    mc-icon
    panel--entity-card
    system-roles--modal 
') 
?>
<panel--entity-card 
    :entity="entity"
    :on-delete-remove-from-lists="false"
    @deleted="$emit('deleted', $event)"
    @published="$emit('deleted', $event)">
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

    <template #entity-actions-right >
        <system-roles--modal 
            v-if="entity.status == 1"
            :entity="entity">
        </system-roles--modal>
    </template>

</panel--entity-card>