<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    entity-terms
    mc-link
    mc-modal
    select-owner-entity
');
?>
<mc-modal :title="modalTitle" classes="create-modal create-opportunity-modal" button-label="<?php i::_e('Criar Oportunidade')?>" @open="createEntity()" @close="destroyEntity()">
    <template v-if="entity && !entity.id" #default="modal">
        <div><?php i::_e('Crie uma oportunidade com informações básicas e de forma rápida') ?></div>
        <form @submit.prevent="handleSubmit" class="create-modal__fields">

            <entity-field :entity="entity" hide-required :editable="true" label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
            <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Título") ?> prop="name"></entity-field>
            
            <div class="create-opportunity-modal__continuous-flow">
                <div class="create-opportunity-modal__continuous-flow-options">
                    <entity-field :entity="entity" type="checkbox" prop="isContinuousFlow" label="<?php i::esc_attr_e('Habilitar fluxo contínuo')?>" :disabled="entity?.publicityOnly">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> fluxo-continuo') ?>
                        </template>
                    </entity-field>
                    <entity-field v-if="entity?.isContinuousFlow" :entity="entity" type="checkbox" prop="hasEndDate" label="<?php i::esc_attr_e('Habilitar data final das inscrições')?>"></entity-field>
                </div>
            </div>

            <div class="create-opportunity-modal__publicity-only">
                <div class="create-opportunity-modal__publicity-only-options">
                    <entity-field :entity="entity" type="checkbox" prop="publicityOnly" label="<?php i::esc_attr_e('Oportunidade apenas para divulgação')?>" :disabled="entity?.isContinuousFlow">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> apenas-divulgacao') ?>
                        </template>
                    </entity-field>
                </div>
            </div>

            <entity-terms :entity="entity" hide-required :editable="true" title="<?php i::_e('Área de Interesse') ?>" taxonomy="area"></entity-terms>

            <select-owner-entity :entity="entity" title="<?php i::esc_attr_e('Vincule a oportunidade a uma entidade: ') ?>"></select-owner-entity>

            <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
        </form>
    </template>

    <template v-if="entity?.id" #default>
        <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.  '); ?></label>
    </template>

    <template v-if="entity?.id && entity.status==0" #default>
        <!-- #rascunho -->
        <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.'); ?></label><br><br>
        <label><?php i::_e('Para completar e publicar sua oportunidade, acesse a área <b>Rascunhos</b> em <b>Minhas Oportunidades</b> no <b>Painel de Controle</b>.  '); ?></label>
    </template>

    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>

    <template v-if="!entity?.id" #actions="modal">
        <!-- #Criado em Rascunho -->
        <button class="button button--primary button--icon " @click="createDraft(modal)"><?php i::_e('Criar') ?></button>
        <button class="button button--text button--text-del" @click="modal.close(); destroyEntity()"><?php i::_e('Cancelar') ?></button>
    </template>

    <template v-if="entity?.id" #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
    </template>

    <template v-if="entity?.id " #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
    </template>
</mc-modal>