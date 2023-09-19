<?php

use MapasCulturais\App;
use MapasCulturais\i;

$this->import('
    mc-icon
    faq-accordion
');
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
        <aside class="faq__aside">
            <button class="faq__btn faq__btn--selected"><?= i::__('Cadastro no Mapa') ?><mc-icon name="arrow-right-ios"></mc-icon></button>
            <button class="faq__btn"><?= i::__('Inscrições em editais em oportunidades') ?> <mc-icon name="arrow-right-ios" class="primary__color faq__arrow"></mc-icon></button>
        </aside>
        <main>
            <faq-accordion></faq-accordion>
            <div class="faq__footer">
                <p class="bold"><?= i::__('Não encontrou o que procurava?') ?></p>
                <div class="faq__footer-btns">
                    <button class="button button--primary "><?= i::__('Contate o nosso suporte') ?></button>
                    <p class="bold"><?= i::__('ou') ?></p>
                    <button class="button button--primary button--primary-noborder"><?= i::__('Contate o nosso suporte') ?></button>
                </div>
            </div>
        </main>
    </div>
</div>