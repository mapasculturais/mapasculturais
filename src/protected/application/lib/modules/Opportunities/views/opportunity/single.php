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
');
$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>

<div class="main-app single opportunity">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity"></entity-header>
    <mapas-container>
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
                    <div v-for="item in [1,2,3]">
                        <p class="opportunity__phases--title"><?= i::__("Avaliação documental") ?></p>
                        <p class="opportunity__phases--description"><?= i::__("de 05/03/2022 a 21/03/2022 às 12:00") ?></p>
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
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <entity-seals :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                        <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                    </div>
                </aside>
            </mapas-container>
        </tab>
    </tabs>

</div>
