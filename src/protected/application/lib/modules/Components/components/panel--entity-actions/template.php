<?php
use MapasCulturais\i;
use MapasCulturais\Entity;

$this->import('confirm-button');
$this->import('loading');
?>

<loading :entity="entity"></loading>

<confirm-button v-if="!entity.__processing && archiveButton && entity.status != <?= Entity::STATUS_ARCHIVED ?>" 
    @confirm="archiveEntity($event)" 
    message="<?php i::esc_attr_e("Você está certo que deseja arquivar esta entidade?") ?>"><?php i::_e('Arquivar') ?></confirm-button>

<confirm-button v-if="!entity.__processing && deleteButton && entity.status != <?= Entity::STATUS_TRASH ?>" 
    @confirm="deleteEntity($event)" 
    message="<?php i::esc_attr_e("Você está certo que deseja excluir esta entidade?") ?>"><?php i::_e('Excluir') ?></confirm-button>

<confirm-button v-if="!entity.__processing && destroyButton && entity.status == <?= Entity::STATUS_TRASH ?>" 
    @confirm="destroyEntity($event)" 
    message="<?php i::esc_attr_e("Você está certo que deseja excluir definitivamente esta entidade?") ?>"><?php i::_e('Excluir permanentemente') ?></confirm-button>

<confirm-button v-if="!entity.__processing && publishButton && entity.status == <?= Entity::STATUS_TRASH ?>" 
    @confirm="publishEntity($event)" 
    message="<?php i::esc_attr_e("Você está certo que deseja recuperar esta entidade da lixeira?") ?>"><?php i::_e('Recuperar') ?></confirm-button>
    
<confirm-button v-if="!entity.__processing && publishButton && entity.status != <?= Entity::STATUS_TRASH ?> && entity.status != <?= Entity::STATUS_ENABLED ?>" 
    @confirm="publishEntity($event)" 
    message="<?php i::esc_attr_e("Você está certo que deseja publicar esta entidade?") ?>"><?php i::_e('Publicar') ?></confirm-button>