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
    entity-log
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
                    <p class="opportunity-basic-info__label">Data de início das inscrições<span class="opportunity-basic-info__hint">*obrigatório</span></p>
                    <datepicker
                            :locale="locale"
                            :weekStart="0"
                            v-model="dateStart"
                            :format="dateFormat"
                            :dayNames="dayNames"
                            autoApply utc>
                    </datepicker>
                </div>
                <div class="col-4 sm:col-12">
                    <p class="opportunity-basic-info__label">Data de termino das inscrições <span class="opportunity-basic-info__hint">*obrigatório</span></p>
                    <datepicker
                            :locale="locale"
                            :weekStart="0"
                            v-model="dateEnd"
                            :format="dateFormat"
                            :dayNames="dayNames"
                            autoApply utc>
                    </datepicker>
                </div>
            </div>
            <div class="grid-12">
                <div class="col-5 sm:col-12">
                    <p class="opportunity-basic-info__label">Publicação final de resultados (data e hora)<span class="opportunity-basic-info__hint">*obrigatório</span></p>
                    <datepicker
                            :locale="locale"
                            :weekStart="0"
                            v-model="dateFinalResult"
                            :format="dateFormat"
                            :dayNames="dayNames"
                            autoApply utc>
                    </datepicker>
                </div>
                <div class="col-4 sm:col-12">
                    <p class="opportunity-basic-info__label">Haverá prestação de contas?</p>
                    <input v-model="accountability" type="radio" id="yes" value="true">
                    <label for="yes">Sim</label>
                    <input v-model="accountability" type="radio" id="no" value="false">
                    <label for="no">Não</label>
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
                    <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Adicionar regulamento');?>" editable></entity-files-list>
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
<!--                <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>-->
                <entity-seals :entity="entity" editable classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags')?>" editable></entity-terms>
                <entity-related-agents editable :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>" editable></entity-related-agents>
                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                <entity-log :entity="entity" classes="col-12"></entity-log>
            </div>
        </mapas-card>
    </aside>
</mapas-container>

