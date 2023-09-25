<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->part('header', $render_data);
$this->part('main-header', $render_data);
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
            <form class="faq__filter <?= isset($active_header) && $active_header ? 'faq__filter--img' : '' ?>" @submit.prevent>
                <input type="text" placeholder="<?= i::__('Pesquise por palavra-chave') ?>" class="faq__search" />
                <button class="faq__search-btn">
                    <mc-icon name="search"></mc-icon>
                </button>
            </form>
        </div>

    </header>
    <?= $TEMPLATE_CONTENT ?>
    <div class="faq__footer">
        <p class="bold"><?= i::__('Não encontrou o que procurava?') ?></p>
        <div class="faq__footer-btns">
            <button class="button button--primary "><?= i::__('Contate o nosso suporte') ?></button>
            <p class="bold"><?= i::__('ou') ?></p>
            <button class="button button--primary button--primary-noborder"><?= i::__('Contate o nosso suporte') ?></button>
        </div>
    </div>

</div>
<?php
$this->part('main-footer', $render_data);
$this->part('footer', $render_data);
