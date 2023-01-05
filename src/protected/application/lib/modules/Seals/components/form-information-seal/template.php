<?php
use MapasCulturais\i;
$this->import('
    mapas-card
    form-valid-period
    entity-field
    seal-activity-card
    image-uploader
    entity-files-list
    entity-links
');
?>

<mapas-card>
    <template #title>
        <label><?= i::__("Informações de selos")?></label>
        <p><?= i::__("Texto exemplo de texto")?></p>
    </template>
    <template #content>
        <div class="left">
            <div class="grid-12">
                <entity-field :entity="entity" classes="col-9 sm:col-12" prop="name"></entity-field>
                <div class="col-12">
                    <h3>Validade do certificado do selo</h3>
                    <form-valid-period classes="col-12" :entity="entity"></form-valid-period>
                </div>
                <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
            </div>
        </div>
    </template>
</mapas-card>

<mapas-card>
    <template #title>
        <label>{{ text('custom_information_title') }}</label>
        <p>{{ text('custom_information_tip') }}</p>
    </template>
    <template #content>
        <div class="left">
            <div class="grid-12">
                <div class="col-12">
                    <seal-activity-card :entity="entity">
                        <template #title>
                            <h4><?= i::__("Texto do certificado") ?></h4>
                        </template>
                        <template #content>
                            <p>
                                <?= i::__("Certificamos que ") ?>
                                <input class="code" type="text" value="[EntityName]" :size="'EntityName'.length" />
                                <?= i::__(" na condição de ") ?>
                                <input class="code" type="text" value="[EntityDefinition]"  :size="'EntityDefinition'.length"/>
                                <?= i::__(" recebeu o selo ") ?>
                                <input class="code" type="text" value="[SealName]"  :size="'SealName'.length"/>
                                <?= i::__(" no dia ") ?>
                                <input class="code" type="text" value="[DateIni]"  :size="'DateIni'.length"/>
                                <?= i::__(" referente a sua participação em ") ?>
                                <input class="code" type="text" value="[SealShortDescription]"  :size="'SealShortDescription'.length"/>.
                                <?= i::__("Esta certificação tem validade até o dia ") ?>
                                <input class="code" type="text" value="[DateFin]"  :size="'DateFin'.length"/>.
                                <?= i::__("Agradecemos sua participação. Atenciosamente, ") ?>
                                <input class="code" type="text" value="[SealOwner]"  :size="'SealOwner'.length"/>
                            </p>
                        </template>
                    </seal-activity-card>
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
</mapas-card>

<mapas-card>
    <template #title>
        <label><?= i::__("Outras informações")?></label>
        <p><?= i::__("Os dados inseridos abaixo também são exibidos publicamente")?></p>
    </template>
    <template #content>
        <div class="grid-12">
            <entity-field :entity="entity" classes="col-12" prop="longDescription"></entity-field>
            <entity-files-list :entity="entity" group="downloads" classes="col-12"  title= "<?php i::_e('Adicionar arquivos para download'); ?>" editable></entity-files-list>
            <div class="col-12">
                <entity-links :entity="entity" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
            </div>
        </div>
    </template>
</mapas-card>