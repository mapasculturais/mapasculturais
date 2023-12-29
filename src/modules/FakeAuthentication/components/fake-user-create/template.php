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

<mc-modal title="Criar Usuario" classes="create-modal" @close="destroyEntity()" @open="createEntity()">
  <p>Crie um usuario com informações básicas
    e de forma rápida</p>
  <form @submit="createUser($event);">
    <div class="field">
      <label for="name">Nome</label>
      <input name="name" v-model="user.name">
    </div>
    <div class="field">
      <label for="email">E-mail</label>
      <input name="email" v-model="user.email">
    </div>
    <div class="wrapper-button-modal">
      <button class="button button--primary button--icon" type="submit">salvar</button>
      <button class="button button--text button--text-del" @click="modal.close()">cancelar</button>
    </div>

  </form>
  <template #button="modal">
    <button class="button button--primary button--icon" @click="modal.open()">
      <mc-icon name="add"></mc-icon> Criar usuario
    </button>
  </template>
</mc-modal>