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
            <div class="faq__img">
                <img src="<?php $this->asset('/img/faq.png', true, true) ?>">
                <div class="faq__title">
                    <h1 class="faq__h1 bold">Está com dúvidas?</h1>
                    <p>Confira nossas perguntas frequentes</p>
                </div>
            </div>
            <form class="faq__filter" @submit.prevent>
                <input type="text" placeholder="<?= i::__('Pesquise por palavra-chave') ?>" class="faq__search" />
                <button class="faq__search-btn">
                    <mc-icon name="search"></mc-icon>
                </button>
            </form>
        </div>

    </header>
    <div class="faq__content">
        <?= $TEMPLATE_CONTENT ?>
    </div>
</div>
<?php
$this->part('main-footer', $render_data);
$this->part('footer', $render_data);