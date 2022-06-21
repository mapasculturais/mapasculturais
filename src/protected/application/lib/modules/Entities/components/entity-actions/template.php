<?php 
use MapasCulturais\i;
?>
<div class="entity-actions">

    <div class="entity-actions__content">
    
        <div class="entity-actions__content--groupBtn">

            <button class="button btn" @click="entity.delete()">
                <?php i::_e("Excluir") ?>
            </button>

        </div>

        <div class="entity-actions__content--groupBtn">

            <button class="button btn" @click="entity.archive()">
                <?php i::_e("Arquivar") ?>
            </button>

            <button class="button btn" @click="entity.save()">
                <?php i::_e("Salvar") ?>
            </button>

            <button class="button btn publish" @click="entity.publish()">
                <?php i::_e("Publicar") ?>
            </button>

        </div>

    </div>

</div>