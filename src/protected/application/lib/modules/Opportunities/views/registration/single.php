<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'registrations';

$this->addOpportunityPhasesToJs();

$this->import('
    mapas-breadcrumb
    mapas-card
    opportunity-header
    tabs
    opportunity-phases-timeline
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>

<div class="main-app registration single">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <tabs>
        <tab label="<?= i::_e('Acompanhamento') ?>" slug="acompanhamento">
            <div class="registration__content">
                <mapas-card no-title>
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
                                            <div class="category__info"> {{entity.category}} </div>
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
                                    <div class="project">
                                        <div class="project__label"> <?= i::__('Nome do projeto') ?> </div>
                                        <div class="project__name project__color"> Nome do projeto aqui </div>
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="status">
                                        <span v-if="entity.status == 0"> <?= i::__('Não enviada') ?> </span>
                                        <span v-if="entity.status > 0"> <?= i::__('Enviada') ?> </span>
                                    </div>
                                    <div class="sentDate"> <?= i::__('Inscrição realizada em') ?> {{entity.sentTimestamp.date('2-digit year')}} <?= i::__('as') ?> {{entity.sentTimestamp.hour('numeric')}}h </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </mapas-card>

                <mapas-card no-title>
                    <template #content>

                        <opportunity-phases-timeline center big></opportunity-phases-timeline>

                    </template>
                </mapas-card>
            </div>
        </tab>

        <tab label="<?= i::_e('Ficha de inscrição') ?>" slug="ficha">
            <div class="registration__content">
                <mapas-card no-title>
                    <template #content>
                        <div class="registered-info">
                            <span class="info"> <strong> <?= i::__('Dados do proponente') ?> </strong> </span>
                            <span class="info"> <strong> <?= i::__('Nome') ?>: </strong> {{entity.owner.name}} </span>
                            <span class="info"> <strong> <?= i::__('Descrição curta') ?>: </strong> {{entity.owner.shortDescription}} </span>
                            <span class="info"> <strong> <?= i::__('CPF ou CNPJ') ?>: </strong> {{entity.owner.document}} </span>
                            <span class="info"> <strong> <?= i::__('Data de nascimento ou fundação') ?>: </strong> <!-- {{entity.owner.dataDeNascimento.date('2-digit year')}} --> </span>
                            <span class="info"> <strong> <?= i::__('Email') ?>: </strong> {{entity.owner.emailPublico}} </span>
                            <span class="info"> <strong> <?= i::__('Raça') ?>: </strong> {{entity.owner.raca}} </span>
                            <span class="info"> <strong> <?= i::__('Genero') ?>: </strong> {{entity.owner.genero}} </span>
                            <span class="info"> <strong> <?= i::__('Endereço') ?>: </strong> {{entity.owner.endereco}} </span>
                            <span class="info"> <strong> <?= i::__('CEP') ?>: </strong> {{entity.owner.En_CEP}} </span>
                        </div>
                    </template>
                </mapas-card>

                <mapas-card>
                    <template #title>
                        <label> <?= i::__('Espaço Vinculado') ?> </label>
                    </template>
                    <template #content>
                        <div class="space">
                            <div class="image">
                                <mc-icon name="image"></mc-icon>
                            </div>
                            <div class="name">
                                Nome do espaço
                            </div>
                        </div>
                    </template>
                </mapas-card>
            </div>
        </tab>
    </tabs>
</div>