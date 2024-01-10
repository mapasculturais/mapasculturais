<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
     entity-field 
     mc-modal
');

?>

<mc-modal title="<?php i::_e("Criação de usuário");?>" classes="create-modal" @close="destroyEntity()" @open="createEntity()">
  <form>
    <div class="field">
      <label for="name"><?php i::_e("Nome");?></label>
      <input name="name" v-model="user.name">
      <small></small>
    </div>
    <div class="field">
      <label for="email"><?php i::_e("E-mail");?></label>
      <input name="email" v-model="user.email">
      <small></small>
    </div>
  </form>


  <template #actions="modal">
    <button class="button button--primary" @click="createUser($event)"><?php i::_e("Criar");?></button>
    <button class="button button--primary" @click="modal.close()"><?php i::_e("Cancelar");?></button>
  </template>

  <template #button="modal">
    <button class="button button--primary button--icon" @click="modal.open()"><?php i::_e("Criar novo Usuario");?></button>
  </template>
</mc-modal>