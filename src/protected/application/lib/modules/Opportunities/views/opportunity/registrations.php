<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label'=> $entity->name, 'url' => $app->createUrl('opportunity', 'registrations', [$entity->id])],
];

$this->import('
    entity-header
    entity-actions
    mapas-breadcrumb
    mc-link
    opportunity-form-builder
    opportunity-header
    v1-embed-tool
')
?>

<div class="main-app opportunity-registrations">
  <mapas-breadcrumb></mapas-breadcrumb>
  <opportunity-header :opportunity="entity">
    <template #button>
      <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="registrations" icon="arrow-left"><?= i::__('Voltar') ?></mc-link>
    </template>
  </opportunity-header>
    <div class="opportunity-registrations__container">
        <div>
            <div class="grid-12">
                <div class="col-6">
                    <h2><?= i::__("Inscrições concluídas") ?></h2>
                </div>
                <div class="col-3 text-right">
                    <button class="button button--secondarylight"><?= i::__("Baixar rascunho") ?></button>
                </div>
                <div class="col-3">
                    <button class="button button--secondarylight"><?= i::__("Baixar lista de inscrições") ?></button>
                </div>
            </div>
        </div>
        <div class="opportunity-registrations__observation">
            <div class="grid-12">
                <div class="col-12">
                    <h5>
                        <?= i::__("Visualize a lista de pessoas inscritas neste edital. E acompanhe os projetos criados para os Agentes Culturais aceitos.") ?>
                    </h5>
                </div>
            </div>
        </div>
        <div>
            <mapas-card>
                <div class="grid-12">
                    <div class="col-12">
                        <v1-embed-tool route="registrationmanager" :id="entity.id"></v1-embed-tool>
                    </div>
                </div>
            </mapas-card>
        </div>
    </div>
</div>
