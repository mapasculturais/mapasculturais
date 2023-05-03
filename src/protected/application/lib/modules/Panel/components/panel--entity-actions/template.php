<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;
use MapasCulturais\Entity;

$this->import('
    confirm-button
    loading
');
?>
<div v-if="entity.__processing" class="panel__entity-actions">
    <loading :entity="entity"></loading>
</div>
<div v-if="!entity.__processing" class="panel__entity-actions">
    <confirm-button v-if="undeleteButton && entity.status == <?= Entity::STATUS_TRASH ?>"
        @confirm="undeleteEntity($event)" 
        button-class="button unpublish button--primary button--icon recover"
        message="<?php i::esc_attr_e("Você está certo que deseja recuperar esta entidade da lixeira?") ?>">
            <?php i::_e('Recuperar') ?>
    </confirm-button>
    
    <confirm-button v-if="publishButton && entity.status != <?= Entity::STATUS_TRASH ?> && entity.status != <?= Entity::STATUS_ENABLED ?>"
        @confirm="publishEntity($event)"
        button-class="button publish button--primary button--icon publish-archived"
        message="<?php i::esc_attr_e("Você está certo que deseja publicar esta entidade?") ?>">
            <?php i::_e('Publicar') ?>
    </confirm-button>
    
    <confirm-button v-if="archiveButton && entity.status != <?= Entity::STATUS_ARCHIVED ?> && hasStatus('archived')"
        @confirm="archiveEntity($event)"
        button-class="button--text archive button--icon button--sm panel__entity-actions--archive"
        message="<?php i::esc_attr_e("Você está certo que deseja arquivar esta entidade?") ?>">
            <mc-icon name="archive"></mc-icon>
            <span><?php i::_e('Arquivar') ?></span>
    </confirm-button>
    
    <confirm-button v-if="deleteButton && entity.status != <?= Entity::STATUS_TRASH ?> && hasStatus('trash')"
        @confirm="deleteEntity($event)"
        button-class="button--text delete button--icon button--sm panel__entity-actions--trash"
        message="<?php i::esc_attr_e("Você está certo que deseja excluir esta entidade?") ?>">
            <mc-icon name="trash"></mc-icon>
            <span><?php i::_e('Excluir') ?></span>
    </confirm-button>
    
    <confirm-button v-if="destroyButton && entity.status == <?= Entity::STATUS_TRASH ?>"
        @confirm="destroyEntity($event)"
        button-class="button--text delete button--icon button--sm panel__entity-actions--trash"
        message="<?php i::esc_attr_e("Você está certo que deseja excluir definitivamente esta entidade?") ?>">
        <mc-icon name="trash"></mc-icon>
        <?php i::_e('Excluir permanentemente') ?>
    </confirm-button>

    <confirm-button v-if="draftButton && entity.status != <?= Entity::STATUS_ARCHIVED ?> && hasStatus('draft')"
        @confirm="draftEntity($event)"
        button-class="button--text archive button--icon button--sm"
        message="<?php i::esc_attr_e("Você está certo que deseja transformar esta entidade em rascunho?") ?>">
            <span><?php i::_e('Tornar rascunho') ?></span>
    </confirm-button>
</div>