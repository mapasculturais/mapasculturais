<?php 
use MapasCulturais\i;

$this->import('loading');
?>
<div class="entity-actions">

    <div class="entity-actions__content">
        <loading :entity="entity"></loading>
        <template v-if="!entity.__processing">
            <div class="entity-actions__content--groupBtn">

                <button v-if="entity.currentUserPermissions?.remove" class="button btn" @click="entity.delete()">
                    <?php i::_e("Excluir") ?>
                </button>

            </div>

            <div class="entity-actions__content--groupBtn">
                <button v-if="entity.currentUserPermissions?.archive" class="button btn" @click="entity.archive()">
                    <?php i::_e("Arquivar") ?>
                </button>

                <button v-if="entity.currentUserPermissions?.modify" class="button btn" @click="entity.save()">
                    <?php i::_e("Salvar") ?>
                </button>

                <button v-if="entity.currentUserPermissions?.publish" class="button btn publish" @click="entity.publish()">
                    <?php i::_e("Publicar") ?>
                </button>

            </div>
        </template>
    </div>

</div>