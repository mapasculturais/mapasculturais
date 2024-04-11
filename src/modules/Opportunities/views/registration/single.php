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
    mc-alert
    mc-avatar
    mc-breadcrumb
    mc-card
    mc-link
    mc-tab
    mc-tabs
    opportunity-header
    opportunity-phases-timeline
    registration-print
    v1-embed-tool
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $entity->opportunity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->opportunity->id])],
    ['label' => i::__('Inscrição')]
];

$entity = $entity->firstPhase;

$today = new DateTime();
?>

<div class="main-app registration single">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>
    <registration-print :registration="entity"></registration-print>
    <mc-tabs sync-hash>
        <mc-tab label="<?= i::_e('Acompanhamento') ?>" slug="acompanhamento">
            <div class="registration__content">
                <mc-card>
                    <template #content>
                        <div class="registration-info">
                            <div class="registration-info__header">
                                <div class="left">
                                    <div class="agent">
                                        <mc-avatar :entity="entity.owner" size="small"></mc-avatar>
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
                                        <div class="category">
                                            <div class="category__label"> <?= i::__('Faixa') ?> </div>
                                            <div v-if="entity.range" class="category__info"> {{entity.range}} </div>
                                            <div v-if="!entity.range" class="category__info"> <?= i::__('Faixa não informada') ?> </div>
                                        </div>
                                        <div class="category">
                                            <div class="category__label"> <?= i::__('Tipo de proponente') ?> </div>
                                            <div v-if="entity.proponentType" class="category__info"> {{entity.proponentType}} </div>
                                            <div v-if="!entity.proponentType" class="category__info"> <?= i::__('Tipo de proponente não informado') ?> </div>
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
                                    <div v-if="entity.sentTimestamp" class="sentDate"> 
                                        <?= i::__('Inscrição realizada em') ?> {{entity.sentTimestamp.date('2-digit year')}} <?= i::__('às') ?> {{entity.sentTimestamp.time('long')}} 
                                    </div>
                                    <div v-if="!entity.sentTimestamp" class="sentDate">
                                        <?= i::__('Inscrição sem data de envio.') ?><br>
                                        <small><em>
                                            <?= i::__('Isto pode ter acontecido por uma mudança do status da inscrição, pelo gestor ou administrador da oportunidade, diretamente do status de rascunho para o de enviada, inválida, não selecionada, suplente ou selecionada sem que o botão de enviar inscrição tenha sido apertado.') ?>
                                        </em></small>
                                    </div>
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
                            <span class="info" v-if="entity.agentsData.owner.name"> 
                                <strong> <?= i::__('Nome') ?>: </strong> 
                                <span>{{entity.agentsData.owner.name}}</span>
                            </span>
                            <span class="info" v-if="entity.agentsData.owner.shortDescription"> 
                                <strong> <?= i::__('Descrição curta') ?>: </strong> 
                                <span>{{entity.agentsData.owner.shortDescription}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.documento || entity.agentsData.owner.cnpj"> 
                                <strong> <?= i::__('CPF ou CNPJ') ?>: </strong> 
                                <span>{{entity.agentsData.owner.documento || entity.agentsData.owner.cnpj}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.dataDeNascimento"> 
                                <strong> <?= i::__('Data de nascimento ou fundação') ?>: </strong> 
                                <span>{{entity.agentsData.owner.dataDeNascimento}}</span><!-- .date('2-digit year') -->
                            </span>  
                            <span class="info" v-if="entity.agentsData.owner.emailPublico"> 
                                <strong> <?= i::__('Email') ?>: </strong> 
                                <span>{{entity.agentsData.owner.emailPublico}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.raca"> 
                                <strong> <?= i::__('Raça') ?>: </strong> 
                                <span>{{entity.agentsData.owner.raca}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.genero"> 
                                <strong> <?= i::__('Genero') ?>: </strong> 
                                <span>{{entity.agentsData.owner.genero}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.endereco"> 
                                <strong> <?= i::__('Endereço') ?>: </strong> 
                                <span>{{entity.agentsData.owner.endereco}}</span>
                            </span>                            
                            <span class="info" v-if="entity.agentsData.owner.En_CEP"> 
                                <strong> <?= i::__('CEP') ?>: </strong> 
                                <span>{{entity.agentsData.owner.En_CEP}}</span>
                            </span>
                        </div>
                    </template>
                </mc-card>

                <mc-card v-if="entity.opportunity.projectName && entity.opportunity.projectName !== 0">
                    <template #content>
                        <div class="registered-info">
                            <span class="info"> 
                                <strong> <?= i::__('Nome do Projeto') ?> </strong> 
                            </span>
                            <span class="info" vf-if="entity.projectName"> 
                                <span> {{entity.projectName}}</span>
                            </span>
                            <span class="info" v-if="!entity.projectName">
                                <?= i::__('Nome do projeto não informado') ?>
                            </div>
                        </div>
                    </template>
                </mc-card>

                <mc-card v-if="entity.opportunity.useAgentRelationColetivo && entity.opportunity.useAgentRelationColetivo !== 'dontUse'"> 
                    <template #title>
                        <label> <?= i::__('Coletivo') ?> </label>
                    </template>
                    <template #content>
                        <div v-if="entity.agentRelations.hasOwnProperty('coletivo') && entity.agentRelations.coletivo[0]" class="space">
                            <mc-avatar :entity="entity.agentRelations.coletivo[0].agent" size="xsmall"></mc-avatar>
                            <div class="name">
                                <a href="entity?.agentRelations.coletivo[0].agent.singleUrl" class="registration__collective-link bold" :class="[entity.agentRelations.coletivo[0]['@entityType'] + '__color']"> {{entity?.agentRelations.coletivo[0].agent.name}} </a>
                            </div>
                        </div>
                        <div v-if="!entity.agentRelations.hasOwnProperty('coletivo')" class="space">
                        <div class="image">
                                <mc-icon name="agent-2"></mc-icon>
                            </div>
                            <div class="name">
                                <?= i::__('Coletivo não informado') ?>
                            </div>
                        </div>
                    </template>
                </mc-card>
                

                <mc-card v-if="entity.opportunity.useAgentRelationInstituicao && entity.opportunity.useAgentRelationInstituicao !== 'dontUse'"> 
                    <template #title>
                        <label> <?= i::__('Instituição responsável') ?> </label>
                    </template>
                    <template #content>
                        <div v-if="entity.agentRelations.hasOwnProperty('instituicao') && entity.agentRelations.instituicao[0]" class="space">
                            <mc-avatar :entity="entity.agentRelations.instituicao[0].agent" size="xsmall"></mc-avatar>
                            <div class="name">
                                <a :href="entity?.agentRelations.instituicao[0].agent.singleUrl" class="registration__institution-link" :class="[entity.agentRelations.instituicao[0]['@entityType'] + '__color']"> {{entity?.agentRelations.instituicao[0].agent.name}} </a>
                            </div>
                        </div>

                        <div v-if="!entity.agentRelations.hasOwnProperty('instituicao')" class="space">
                            <div class="image">
                                <mc-icon name="agent"></mc-icon>
                            </div>
                            <div class="name">
                                <?= i::__('Instituição responsável não informada') ?>
                            </div>
                        </div>
                    </template>
                </mc-card>

                <mc-card v-if="entity.opportunity.useSpaceRelationIntituicao && entity.opportunity.useSpaceRelationIntituicao !== 'dontUse'"> 
                    <template #title>
                        <label> <?= i::__('Espaço Vinculado') ?> </label>
                    </template>
                    <template #content>
                        <div v-if="entity.relatedSpaces[0]" class="space">
                            <mc-avatar :entity="entity.relatedSpaces[0]" size="xsmall"></mc-avatar>
                            <div class="name">
                                <a href="entity?.relatedSpaces[0]?.singleUrl" class="registration__space-link" :class="[entity.relatedSpaces[0]['@entityType'] + '__color']"> {{entity?.relatedSpaces[0]?.name}} </a>
                            </div>
                        </div>

                        <div v-if="!entity.relatedSpaces[0]" class="space">
                        <div class="image">
                                <mc-icon name="space"></mc-icon>
                            </div>
                            <div class="name">
                                <?= i::__('Sem espaço vinculado') ?>
                            </div>
                        </div>
                    </template>
                </mc-card>

                <?php $phase = $entity;
                while($phase): $opportunity = $phase->opportunity;?>
                    <?php if($opportunity->isDataCollection && $today >= $opportunity->registrationFrom):?>
                        <?php if($opportunity->isFirstPhase):?>
                            <h2><?= i::__('Inscrição') ?></h2>
                        <?php else: ?>
                            <h2><?= $opportunity->name ?></h2>
                        <?php endif ?>
                        <?php if($phase->status < 1 && !$opportunity->isFirstPhase): ?>
                            <mc-alert type="warning">
                                <?= i::__('Nesta etapa, é necessário inserir informações. Por favor, clique no botão para acessar o formulário e preenchê-lo') ?> <br>
                                <?= i::__('dentro do período de') ?>  <?=$phase->opportunity->registrationFrom->format("d/m/Y")?> <?= i::__('à') ?> <?=$phase->opportunity->registrationTo->format("d/m/Y H:i:s")?>
                            </mc-alert>
                            <div class="grid-12">
                                <div class="col-3 sm:col-12">
                                    <a class="button button--primary" href="<?=$app->createUrl("registration", "edit", [$phase->id])?>"><?= i::__('Preencher formulário') ?></a>
                                </div>
                            </div>
                        <?php else: ?>
                            <v1-embed-tool route="registrationview" :id="<?=$phase->id?>"></v1-embed-tool>
                        <?php endif ?>
                    <?php endif ?>
                    <?php $phase = $phase->nextPhase; ?>
                <?php endwhile ?>

            </div>
        </mc-tab>

        <mc-tab v-if="entity.opportunity.currentUserPermissions['@control']" label="<?= i::_e('Avaliadores') ?>" slug="valuers">
            <div class="registration__content">
                <?php $phase = $entity; 
                    while($phase): $opportunity = $phase->opportunity;?>
                    <mc-card>
                        <?php if($today >= $opportunity->registrationFrom):?>
                            <?php if($opportunity->isFirstPhase):?>
                                <h2><?= i::__('Inscrição') ?></h2>
                            <?php else: ?>
                                <h2><?= $opportunity->name ?></h2>
                            <?php endif ?>

                            <v1-embed-tool route="valuers" :id="<?=$phase->id?>"></v1-embed-tool>
                        <?php endif ?>
                        <?php $phase = $phase->nextPhase; ?>
                    </mc-card>
                <?php endwhile ?>
            </div>
        </mc-tab>
    </mc-tabs>
</div>