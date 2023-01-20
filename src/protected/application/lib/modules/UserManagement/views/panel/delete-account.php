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
            <div class="title__title"> <?php i::esc_attr_e('Exclua sua conta') ?> </div>
          </div>
          <div class="help">
            <a class="panel__help-link" href="#"><?=i::__('Ajuda?')?></a>
          </div>
        </div>
      </header>
      <user-management--delete :user="entity"></user-management--delete>

    </div>
  </div>
</div>