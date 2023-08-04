<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-breadcrumb
    mc-card
');


$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Termos e Condições')],
    ['label' => $config['title'], 'url' => $this->controller->createUrl($this->controller->action)],
];

?>
<mc-breadcrumb></mc-breadcrumb>

<div class="mapas-terms">

    <header class="mapas-terms__header">
        <div class="mapas-terms__header-title">
            <div class="title">
                <div class="title__title"> <?= $config['title'] ?> </div>
            </div>
        </div>
    </header>
    <div class="tabs-component tabs mapas-terms__content">
        <div class="tabs-component__panels">
            <section class="tab-component">
                <div class="term">
                    <div class="term__content" style="margin: 1em;">
                        <mc-card><?= $config['text'] ?></mc-card>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>