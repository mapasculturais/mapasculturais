<?php
use MapasCulturais\i;
$this->import('
    entity-cover
    entity-profile
    entity-field
    entity-terms
    entity-social-media
    entity-seals
    entity-terms
    entity-related-agents
    entity-owner
    entity-files-list
    entity-gallery-video
    entity-gallery
    entity-links
');
?>

<div class="opportunity-basic-info__container">
    <mapas-card>
        <template #title>
            <h3><?= i::__("Informações obrigatórias") ?></h3>
        </template>
        <template #content>
            <div class="grid-12">
                <div class="col-4 sm:col-12">
                    <entity-field :entity="entity" prop="registrationFrom" :max="entity.registrationTo?._date" :autosave="300"></entity-field>
                </div>
                <div class="col-4 sm:col-12">
                    <entity-field :entity="entity" prop="registrationTo" :min="entity.registrationFrom?._date" :autosave="300"></entity-field>
                </div>
            </div>
            <div class="grid-12">
                <div class="col-5 sm:col-12" v-if="lastPhase">
                    <entity-field :entity="lastPhase" prop="publishTimestamp" :autosave="300">
                        <p class="opportunity-basic-info__label">
                          <?= i::__("Publicação final de resultados (data e hora)")?>
                            <span class="opportunity-basic-info__hint">
                              <?= i::__("*obrigatório") ?>
                            </span>
                        </p>
                    </entity-field>
                </div>
            </div>
        </template>
    </mapas-card>
</div>
<mapas-container>
    <main>
        <mapas-card>
            <template #content>
                <div class="grid-12 v-bottom">
                    <entity-cover :entity="entity" classes="col-12"></entity-cover>
                    <div class="col-3 sm:col-12">
                        <entity-profile :entity="entity"></entity-profile>
                    </div>
                    <div class="col-9 sm:col-12">
                        <entity-field :entity="entity" prop="name"></entity-field>
                        <entity-field :entity="entity" editable label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
                    </div>
                    <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                    <!-- <entity-files-list :entity="entity" classes="col-12" group="rules"  title="<?php i::esc_attr_e('Adicionar regulamento');?>" editable></entity-files-list> -->
                    <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Adicionar arquivos');?>" editable></entity-files-list>
                    <div class="col-12">
                        <entity-links :entity="entity" title="<?php i::esc_attr_e('Adicionar links'); ?>" editable></entity-links>
                    </div>
                    <entity-gallery-video :entity="entity" classes="col-12" editable></entity-gallery-video>
                    <entity-gallery :entity="entity" classes="col-12" editable></entity-gallery>
                </div>
            </template>
        </mapas-card>
    </main>
    <aside>
        <mapas-card>
            <div class="grid-12">
                <entity-terms :entity="entity" taxonomy="area" classes="col-12" title="<?php i::esc_attr_e('Áreas de interesse'); ?>" editable></entity-terms>
               <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                <entity-seals :entity="entity" editable classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags')?>" editable></entity-terms>
                <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>" editable></entity-related-agents>
                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
            </div>
        </mapas-card>
    </aside>
</mapas-container>

