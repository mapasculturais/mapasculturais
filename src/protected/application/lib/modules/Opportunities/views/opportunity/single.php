<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-header
    mapas-breadcrumb
    tabs
    entity-files-list
    share-links
    entity-gallery-video
    entity-gallery
    entity-social-media
    entity-seals
    entity-related-agents
    entity-owner
    entity-terms
    timeline
    entity-actions
    entity-request-ownership
');
$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>

<div class="main-app single">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity"></entity-header>
    <mapas-container class="opportunity">
        <main>
            <div class="grid-12">
                <div class="col-12">
                    <p class="opportunity__period--title">
                      <?= i::__("Período de inscrições") ?>
                    </p>
                    <div class="opportunity__period--content">
                        <p class="opportunity__period--description">
                            <?= i::__("Inscrições abertas de 05/03/2022 a 21/03/2022 às 12:00") ?>
                        </p>
                    </div>
                </div>
                <div class="col-12">
                    <p class="opportunity__subscription--title">
                        <?= i::__("Inscreva-se") ?>
                    </p>
                    <p class="opportunity__subscription--description">
                        <?= i::__("Você precisa acessar sua conta ou  criar uma cadastro na plataforma para poder se inscrever em editais ou oportunidades") ?>
                    </p>
                    <button class="button button--primary">
                        <?= i::__("Fazer inscrição") ?>
                    </button>
                </div>
            </div>
        </main>
        <aside>
            <div class="grid-12">
                <div class="col-12 opportunity__phases">
                    <div>
                        <timeline
                            :timeline-items="[{
                                from: new Date(2017, 5, 2),
                                title: 'Name',
                                description:
                                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius earum architecto dolor, vitae magnam voluptate accusantium assumenda numquam error mollitia, officia facere consequuntur reprehenderit cum voluptates, ea tempore beatae unde.',
                                color: '#2ecc71',
                                showDayAndMonth: true
                                }]"
                            :message-when-no-items="'NO'"
                            :unique-year="true"
                            order="asc">
                        </timeline>
                    </div>
                </div>
                <div class="col-12">
                    <button class="button button--primary-outline">
                      <?= i::__("Baixar regulamento") ?>
                    </button>
                </div>
            </div>
        </aside>
    </mapas-container>

    <tabs class="tabs">
        <tab label="<?= i::__('Informações') ?>" slug="info">
            <mapas-container>
                <main>
                    <div class="grid-12">
                        <div class="col-12">
                            <h3><?= i::__("Apresentação") ?></h3>
                            <p>{{ entity.shortDescription }}</p>
                        </div>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                        <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                        <div class="col-12">
                            <entity-request-ownership></entity-request-ownership>
                        </div>
                        <div class="col-12 opportunity__helpers">
                            <button class="button button--primary-outline">Denúncia</button>
                            <button class="button button--primary">Contato</button>
                        </div>
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <entity-terms :entity="entity" taxonomy="area" classes="col-12" title="<?php i::esc_attr_e('Áreas de interesse'); ?>"></entity-terms>
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <entity-seals :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags')?>"></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                        <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                    </div>
                </aside>
            </mapas-container>
        </tab>
        <tab label="<?= i::__('Projetos contemplados') ?>" slug="project">
        </tab>
        <tab label="<?= i::__('Suporte') ?>" slug="support">
        </tab>
    </tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>
