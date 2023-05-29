<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'registrations';

$this->addOpportunityPhasesToJs();
$this->addRegistrationPhasesToJs();

$this->import('
    mc-breadcrumb
    mc-card
    mc-tab
    mc-tabs
    opportunity-header
    opportunity-phases-timeline
    v1-embed-tool
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $entity->opportunity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->opportunity->id])],
    ['label' => i::__('Inscrição')]
];
?>

<div class="main-app registration single">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <mc-tabs>
        <mc-tab label="<?= i::_e('Acompanhamento') ?>" slug="acompanhamento">
            <div class="registration__content">
                <mc-card>
                    <template #content>
                        <div class="registration-info">
                            <div class="registration-info__header">
                                <div class="left">
                                    <div class="agent">
                                        <div class="agent__image">
                                            <img v-if="entity.owner.files.avatar" :src="entity.owner.files?.avatar?.transformations?.avatarMedium?.url" />
                                            <mc-icon v-if="!entity.owner.files.avatar" name="image"></mc-icon>
                                        </div>
                                        <div class="agent__name"> {{entity.owner.name}} </div>
                                    </div>
                                    <div class="metadata">
                                        <div class="reg">
                                            <div class="reg__label"> <?= i::__('Nº de inscrição') ?> </div>
                                            <div class="reg__info"> {{entity.number}} </div>
                                        </div>
                                        <div class="category">
                                            <div class="category__label"> <?= i::__('Categoria de inscrição') ?> </div>
                                            <div v-if="entity.category" class="category__info"> {{entity.category}} </div>
                                            <div v-if="!entity.category" class="category__info"> <?= i::__('Sem categoria') ?> </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="status">
                                        <span v-if="entity.status == 0"> <?= i::__('Não enviada') ?> </span>
                                        <span v-if="entity.status > 0"> <?= i::__('Enviada') ?> </span>
                                    </div>
                                </div>
                            </div>
                            <div class="registration-info__footer">
                                <div class="left">
                                    <div v-if="entity.projectName" class="project">
                                        <div class="project__label"> <?= i::__('Nome do projeto') ?> </div>
                                        <div class="project__name project__color"> {{entity.projectName}} </div>
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="status">
                                        <span v-if="entity.status == 0"> <?= i::__('Não enviada') ?> </span>
                                        <span v-if="entity.status > 0"> <?= i::__('Enviada') ?> </span>
                                    </div>
                                    <div class="sentDate"> <?= i::__('Inscrição realizada em') ?> {{entity.sentTimestamp.date('2-digit year')}} <?= i::__('as') ?> {{entity.sentTimestamp.time('long')}} </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </mc-card>

                <mc-card>
                    <template #content>

                        <opportunity-phases-timeline center big></opportunity-phases-timeline>

                    </template>
                </mc-card>
            </div>
        </mc-tab>

        <mc-tab label="<?= i::_e('Ficha de inscrição') ?>" slug="ficha">
            <div class="registration__content">
                <mc-card>
                    <template #content>
                        <div class="registered-info">
                            <span class="info"> 
                                <strong><?= i::__('Dados do proponente') ?></strong> 
                            </span>
                            <span class="info"> 
                                <strong> <?= i::__('Nome') ?>: </strong> 
                                <span v-if="entity.owner.name">{{entity.owner.name}}</span>
                            </span>
                            <span class="info"> 
                                <strong> <?= i::__('Descrição curta') ?>: </strong> 
                                <span v-if="entity.owner.shortDescription">{{entity.owner.shortDescription}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('CPF ou CNPJ') ?>: </strong> 
                                <span v-if="entity.owner.document">{{entity.owner.document}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('Data de nascimento ou fundação') ?>: </strong> 
                                <span v-if="entity.owner.dataDeNascimento">{{entity.owner.dataDeNascimento.date('2-digit year')}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('Email') ?>: </strong> 
                                <span v-if="entity.owner.emailPublico">{{entity.owner.emailPublico}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('Raça') ?>: </strong> 
                                <span v-if="entity.owner.raca">{{entity.owner.raca}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('Genero') ?>: </strong> 
                                <span v-if="entity.owner.genero">{{entity.owner.genero}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('Endereço') ?>: </strong> 
                                <span v-if="entity.owner.endereco">{{entity.owner.endereco}}</span>
                            </span>                            
                            <span class="info"> 
                                <strong> <?= i::__('CEP') ?>: </strong> 
                                <span v-if="entity.owner.En_CEP">{{entity.owner.En_CEP}}</span>
                            </span>
                        </div>
                    </template>
                </mc-card>

                <mc-card>
                    <template #title>
                        <label> <?= i::__('Espaço Vinculado') ?> </label>
                    </template>
                    <template #content>
                        <div v-if="entity.relatedSpaces[0]" class="space">
                            <div class="image">
                                <mc-icon v-if="!entity.relatedSpaces[0]?.files?.avatar" name="image"></mc-icon>
                                <img v-if="entity.relatedSpaces[0]?.files?.avatar" :src="entity.relatedSpaces[0].files.avatar.transformations.avatarMedium.url" />
                            </div>
                            <div class="name">
                                <a href="entity?.relatedSpaces[0]?.singleUrl" :class="[entity.relatedSpaces[0]['@entityType'] + '__color']"> {{entity?.relatedSpaces[0]?.name}} </a>
                            </div>
                        </div>

                        <div v-if="!entity.relatedSpaces[0]" class="space">
                        <div class="image">
                                <mc-icon name="image"></mc-icon>
                            </div>
                            <div class="name">
                                <?= i::__('Sem espaço vinculado') ?>
                            </div>
                        </div>
                    </template>
                </mc-card>

                <v1-embed-tool route="registrationview" :id="entity.id"></v1-embed-tool>
            </div>
        </mc-tab>
    </mc-tabs>
</div>