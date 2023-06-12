<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-cover
    entity-field
    entity-files-list
    entity-links
    entity-profile
    mc-card
    seal-activity-card
    seal-form-valid-period
');
?>
<mc-card>
    <template #title>
        <label><?= i::__("Informações de selos")?></label>
        <p><?= i::__("Texto exemplo de texto")?></p>
    </template>
    <template #content>
        <div class="grid-12 v-bottom">
            <entity-cover :entity="entity" classes="col-12"></entity-cover>
            <div class="col-3 sm:col-12">
                <entity-profile :entity="entity"></entity-profile>
            </div>
            <div class="col-9 sm:col-12">
                <entity-field :entity="entity" prop="name"></entity-field>
                <div>
                    <h3>Validade do certificado do selo</h3>
                    <seal-form-valid-period :entity="entity"></seal-form-valid-period>
                </div>
            </div>
            <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
        </div>
    </template>
</mc-card>

<mc-card>
    <template #title>
        <label>{{ text('custom_information_title') }}</label>
        <p>{{ text('custom_information_tip') }}</p>
    </template>
    <template #content>
        <div class="left">
            <div class="grid-12">
                <div class="col-12">
                    <entity-field :entity="entity" classes="col-12" prop="certificateText"></entity-field>
                </div>
                <div class="col-12">
                    <h4><?= i::__("Palavras-chaves disponíveis:") ?></h4>
                    <div class="grid-2">
                        <div>
                            <span class="code">[SealName]</span>
                            <?= i::__(" Nome do selo") ?>
                        </div>
                        <div>
                            <span class="code">[SealRelationLink]</span>
                            <?= i::__(" Link relacionado ao selo") ?>
                        </div>
                        <div>
                            <span class="code">[SealShortDescription]</span>
                            <?= i::__(" Descrição curta do selo") ?>
                        </div>
                        <div>
                            <span class="code">[EntityDefinition]</span>
                            <?= i::__(" Tipo da entidade contemplada") ?>
                        </div>
                        <div>
                            <span class="code">[SealOwner]</span>
                            <?= i::__(" Dona ou dono do selo") ?>
                        </div>
                        <div>
                            <span class="code">[DateIni]</span>
                            <?= i::__(" Data de início") ?>
                        </div>
                        <div>
                            <span class="code">[EntityName]</span>
                            <?= i::__(" Nome da entidade contemplada") ?>
                        </div>
                        <div>
                            <span class="code">[DateFin]</span>
                            <?= i::__(" Data de final") ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</mc-card>

<mc-card>
    <template #title>
        <label><?= i::__("Outras informações")?></label>
        <p><?= i::__("Os dados inseridos abaixo também são exibidos publicamente")?></p>
    </template>
    <template #content>
        <div class="grid-12">
            <entity-field :entity="entity" classes="col-12" prop="longDescription"></entity-field>
            <entity-files-list :entity="entity" group="downloads" classes="col-12"  title= "<?php i::_e('Adicionar arquivos para download'); ?>" editable></entity-files-list>
            <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
        </div>
    </template>
</mc-card>