<?php
use MapasCulturais\i;
$this->layout = 'panel';

$this->import('user-management--delete');

?>
<div class="p-user-detail">
  <div class="panel-main">
    <div class="panel-page">
      <header class="panel-page__header">
        <div class="panel-page__header-title">
          <div class="title">
            <div class="title__icon default"> <mc-icon name="trash"></mc-icon> </div>
            <h1 class="title__title"> <?php i::esc_attr_e('Exclua sua conta') ?> </h1>
          </div>
        </div>
      </header>
      <user-management--delete :user="entity"></user-management--delete>

    </div>
  </div>
</div>