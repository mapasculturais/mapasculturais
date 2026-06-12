<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
');
?>
<div class="user-management__account-deletion-config" v-if="canConfigure">
    <div class="panel-page__header user-management__account-deletion-header">
        <div class="panel-page__header-title">
            <h3 class="title__title"> <?= i::__('Exclusão de contas (LGPD)') ?> </h3>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::__('Configure o e-mail que receberá as solicitações de exclusão de conta. Se deixar em branco, as solicitações serão enviadas aos administradores do subsite ou, sem subsite, a todos os usuários com papel administrativo na plataforma.') ?>
        </p>
    </div>

    <div class="user-management__account-deletion-form">
        <div class="field">
            <label>
                <?= i::__('E-mail do responsável pela análise e exclusão') ?>
                <small v-if="scopeLabel">({{scopeLabel}})</small>
            </label>
            <div class="user-management__account-deletion-row">
                <input 
                    type="email" 
                    v-model="email" 
                    :disabled="saving"
                    :placeholder="hasSubsite ? 'exemplo@org.br' : text('globalConfigPlaceholder')"
                >
                <button class="button button--primary button--md" :disabled="saving" @click="save">
                    <span v-if="!saving"><?php i::_e('Salvar') ?></span>
                    <span v-if="saving"><?php i::_e('Salvando...') ?></span>
                </button>
            </div>
            <p class="user-management__account-deletion-help" v-if="!hasSubsite">
                <?= i::__('Este sistema não utiliza subsites. O e-mail configurado aqui será usado globalmente.') ?>
            </p>
        </div>
    </div>
</div>
