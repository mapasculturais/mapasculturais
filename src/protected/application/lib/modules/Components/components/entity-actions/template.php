<?php 
use MapasCulturais\i;
?>
<div class="entity-actions">
    
    <div class="entity-actions__groupBtn">

        <button class="button entity-actions__groupBtn--btn" @click="del()">
            <?php i::_e("Excluir") ?>
        </button>

    </div>

    <div class="entity-actions__groupBtn">

        <button class="button entity-actions__groupBtn--btn" @click="archive()">
            <?php i::_e("Arquivar") ?>
        </button>

        <button class="button entity-actions__groupBtn--btn" @click="save()">
            <?php i::_e("Salvar") ?>
        </button>

        <button class="button entity-actions__groupBtn--btn publish" @click="publish()">
            <?php i::_e("Publicar") ?>
        </button>

    </div>

</div>