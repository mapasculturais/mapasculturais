<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->part('header', $render_data);
$this->part('main-header', $render_data);

$this->import('
    faq-search
');
?>

<div class="faq">
    <header class="faq__header">
        <div class="faq__find">
            <?php if (isset($active_header) && $active_header) : ?>
                <div class="faq__img">
                    <img src="<?php $this->asset('/img/faq.png', true, true) ?>">
                    <div class="faq__title">
                        <h1 class="faq__h1 bold"><?= i::__('Está com dúvidas?') ?></h1>
                        <p><?= i::__('Confira nossas perguntas frequentes') ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="faq__filter <?= isset($active_header) && $active_header ? 'faq__filter--img' : '' ?>">
                <faq-search></faq-search>
            </div>
        </div>
    </header>
    <?= $TEMPLATE_CONTENT ?>
</div>
<?php
$this->part('main-footer', $render_data);
$this->part('footer', $render_data);
