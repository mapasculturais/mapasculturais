<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = "entity";

$this->import('
    mc-container
    opportunity-header
    registration-info
    v1-embed-tool
');

?>

<div class="main-app form-preview">

    <opportunity-header :opportunity="entity"></opportunity-header>

    <div class="form-preview__content">

        <mc-container>
            <main class="grid-12">
                <registration-info :registration="entity" classes="col-12"></registration-info>

                <mc-card v-if="entity.useAgentRelationColetivo && entity.useAgentRelationColetivo != 'dontUse'">
                    <template #title>
                        <h3 class="bold"><?php i::_e("Agente coletivo"); ?></h3>
                        <p><?php i::_e("Agente coletivo sem CNPJ, com os campos Data de Nascimento/Fundação e Email Privado obrigatoriamente preenchidos."); ?></p>
                    </template>
                    <template #content>
                        <button class="button button--primary-outline button--icon button--md disabled" disabled>
                            <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?>
                        </button>
                    </template>
                </mc-card>

                <mc-card v-if="entity.useAgentRelationInstituicao && entity.useAgentRelationInstituicao != 'dontUse'">
                    <template #title>
                        <h3 class="bold"><?php i::_e("Instituição responsável"); ?></h3>
                        <p><?php i::_e("Agente coletivo (pessoa jurídica) com os campos CNPJ, Data de Nascimento/Fundação, Email Privado e Telefone 1 obrigatoriamente preenchidos."); ?></p>
                    </template>
                    <template #content>
                        <button class="button button--primary-outline button--icon button--md disabled" disabled>
                            <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?>
                        </button>
                    </template>
                </mc-card>

                <mc-card v-if="entity.useSpaceRelationIntituicao && entity.useSpaceRelationIntituicao != 'dontUse'">
                    <template #title>
                        <h3 class="bold"><?php i::_e("Adicionar um espaço"); ?></h3>
                        <p><?php i::_e("Adicione um Espaço para vincular à sua inscrição."); ?></p>
                    </template>
                    <template #content>
                        <button class="button button--primary-outline button--icon button--md disabled" disabled>
                            <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?>
                        </button>
                    </template>
                </mc-card>

                <mc-card v-if="entity.projectName && entity.projectName != 0">
                    <template #title>
                        <h3 class="bold"><?php i::_e("Nome do Projeto"); ?></h3>
                        <p><?php i::_e("Informe o nome do seu projeto."); ?></p>
                    </template>
                    <template #content>
                        <button class="button button--primary-outline button--icon button--md disabled" disabled>
                            <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?>
                        </button>
                    </template>
                </mc-card>

                <section class="col-12">
                    <v1-embed-tool iframe-id="preview-form" route="registrationformpreview" :id="entity.id"></v1-embed-tool>
                </section>
            </main>

            <aside>
                <a href="<?= $app->createUrl('opportunity', 'formBuilder', [$entity->id]) ?>" class="button button--large button--primary-outline">
                    <?= i::__("Voltar") ?>
                </a>
            </aside>
        </mc-container>
    </div>
</div>