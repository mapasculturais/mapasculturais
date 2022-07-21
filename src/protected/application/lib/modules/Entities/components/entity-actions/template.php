<?php 
use MapasCulturais\i;

$this->import('loading');
?>
<div class="entity-actions">

    <div class="entity-actions__content">
        <loading :entity="entity"></loading>
        <template v-if="!entity.__processing">
            <div class="entity-actions__content--groupBtn rowBtn">
                <button v-if="entity.currentUserPermissions?.archive" class="button button--icon button--sm arquivar">
                    <iconify icon="mi:archive"></iconify>
                    <?php i::_e("Arquivar")?>
                </button>

                <button v-if="entity.currentUserPermissions?.remove" class="button button--icon button--sm excluir">
                    <iconify icon="ooui:trash"></iconify>
                    <?php i::_e("Excluir")?>
                </button>
            </div>

            <div class="entity-actions__content--groupBtn">
                <button v-if="!entity.status" class="button button--secondary btn">
                    <?php i::_e("Sair") ?>
                </button>

                <button v-if="entity.currentUserPermissions?.modify" class="button button--secondary btn" @click="entity.save()">
                    <?php i::_e("Salvar") ?>
                </button>

                <button v-if="entity.currentUserPermissions?.modify && !entity.status" class="button btn publish" @click="entity.publish()">
                    <?php i::_e("Publicar") ?>
                </button>
                <button v-if="entity.status" class="button btn publish publish-exit" @click="entity.publish()">
                    <?php i::_e("Concluir Edição e Sair") ?>
                </button>
            </div>
        </template>
    </div>

</div>