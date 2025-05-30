<?php /**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 use MapasCulturais\i;
?>
<div v-if="showButton()" class="editable-fields">
    <button  @click="editForm()" class="button button--primary"><?= i::__('Editar informações enviadas') ?></button>
    <small><i><?= i::__('O prazo para editar as informações termina em') ?> {{formatEditableUntil}}</i></small>
    
</div>