<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    confirm-before-exit
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-links
    entity-owner
    entity-profile
    entity-related-agents
    entity-seals
    entity-social-media
    entity-status
    entity-terms
    entity-terms
    link-opportunity
    mc-container
    opportunity-rules    
');
?>
<div class="opportunity-basic-info__container">
<entity-status :entity="entity"></entity-status>

    <mc-card>
        <template #title>
            <h3><?= i::__("Informações obrigatórias") ?></h3>
        </template>
        <template #content>
            <?php $this->applyTemplateHook('opportunity-basic-info','before')?>
            <div class="grid-12">
                <?php $this->applyTemplateHook('opportunity-basic-info','begin')?>
                <entity-field :entity="entity" prop="registrationFrom" :max="entity.registrationTo?._date" :autosave="3000" classes="col-6 sm:col-12"></entity-field>
                <entity-field :entity="entity" prop="registrationTo" :min="entity.registrationFrom?._date" :autosave="3000" classes="col-6 sm:col-12"></entity-field>

                <entity-field v-if="lastPhase" :entity="lastPhase" prop="publishTimestamp" :autosave="3000" classes="col-6">
                    <label><?= i::__("Publicação final de resultados (data e hora)") ?></label>
                </entity-field>
                <?php $this->applyTemplateHook('opportunity-basic-info','afeter')?>
            </div>
            <?php $this->applyTemplateHook('opportunity-basic-info','end')?>
        </template>
    </mc-card>
</div>
<mc-container>
    <main>

        <mc-card>
            <template #content>
                <div class="header-opp grid-12 v-bottom">
                    <entity-cover :entity="entity" classes=" header-opp__cover col-12"></entity-cover>
                    <div class="header-opp__profile col-3 sm:col-12">
                        <entity-profile :entity="entity"></entity-profile>
                    </div>
                    <div class="header-opp__field grid-12 col-9 sm:col-12">
                        <entity-field :entity="entity" prop="name" classes="header-opp__field--name col-12"></entity-field>
                        <entity-field :entity="entity" label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type" classes="header-opp__field--name col-12"></entity-field>
                    </div>
                    <entity-field :entity="entity" classes="header-opp__field--name col-12" prop="shortDescription"></entity-field>
                    <entity-files-list :entity="entity" classes="content-fileList col-12" group="downloads" title="<?php i::esc_attr_e('Adicionar arquivos'); ?>" editable></entity-files-list>
                    <entity-links :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Adicionar links'); ?>" editable></entity-links>
                    <entity-gallery-video :entity="entity" classes="col-12" editable></entity-gallery-video>
                    <entity-gallery :entity="entity" classes="col-12" editable></entity-gallery>
                </div>
            </template>
        </mc-card>
    </main>
    <aside>
        <mc-card>
            <div class="grid-12">
                <link-opportunity :entity="entity" editable class="col-12"></link-opportunity>
                <opportunity-rules :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Adicionar regulamento'); ?>" editable></opportunity-rules>
                <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>" editable></entity-related-agents>
                <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags') ?>" editable></entity-terms>
                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
            </div>
        </mc-card>
    </aside>
</mc-container>
<confirm-before-exit :entity="entity"></confirm-before-exit>
