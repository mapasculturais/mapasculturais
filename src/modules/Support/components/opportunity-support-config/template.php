<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-avatar
	mc-icon 
	mc-title
');
?>

<div>
    <div>
        <h2>Configuração de suporte</h2>
        <p>Adicione Agentes que darão suporte à essa Oportunidade.</p>
    </div>
    <button class="button button--primary button-icon"><mc-icon name="add"></mc-icon> <?= i::__("Adicionar Agente") ?> </button>
    <div class="entity-card" :class="classes">
        <div class="entity-card__header" :class="{'with-labels': useLabels, 'without-labels': !useLabels}">
            <div class="entity-card__header user-details">
                <slot name="avatar">
                    <mc-avatar :entity="entity" size="small"></mc-avatar>
                </slot>
                <mc-title tag="h2" :shortLength="55" :longLength="71" class="bold">{{entity.name}}</mc-title>
            </div>
        </div>
    </div>
    <button class="button button--primary button--icon"> <?= i::__("Gerenciar permissões") ?> </button>
    <button class="button button--delete"><mc-icon name="trash"></mc-icon><?= i::__("Excluir") ?> </button>
</div>