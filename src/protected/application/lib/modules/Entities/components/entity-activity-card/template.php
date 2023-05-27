<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
');
?>
<div class="entity-activity-card">
    <div class="entity-activity-card__header">
        <label class="entity-activity-card__header--title">Atividades do projeto</label>
    </div>
    <div class="entity-activity-card__content ">
        <div class="entity-activity-card__content--header">
            <label></label>
        </div>
        <div class="entity-activity-card__content--info">
            
        </div>
    </div>
    <div class="entity-activity-card__aside">
        <div class="entity-activity-card__aside--left">
            <mc-icon name="edit"></mc-icon>
            <label class="entity-activity-card__aside--left-label">Editar</label>

        </div>
        <div class="entity-activity-card__aside--right">
            <mc-icon name="trash"></mc-icon><label>Excluir</label>

        </div>
    </div>
</div>