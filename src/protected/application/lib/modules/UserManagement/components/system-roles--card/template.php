<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    panel--entity-card
    system-roles--modal 
') 
?>
<panel--entity-card :entity="entity" :on-delete-remove-from-lists="false" @deleted="$emit('deleted', $event)" @published="$emit('deleted', $event)">
    <code>ID {{entity.id}}</code>
    <code>slug: {{entity.slug}}</code>
    <a class="system-roles-card__close" v-if="showItem" @click="toggle()"><label class="system-roles-card__label">Permissões</label><mc-icon name="arrowPoint-up"></mc-icon></a>
    <a class="system-roles-card__expand" v-if="!showItem"  @click="toggle()"><label>Permissões</label> <mc-icon name="arrowPoint-down"></mc-icon></a>
    <div class="grid-12 system-roles-card__content" v-if="showItem">
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