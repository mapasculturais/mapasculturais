<?php
use MapasCulturais\i;
?>
<header class="entity-header" :class="{ 'entity-header--no-image': false }">
    <div class="entity-header__cover" :style="{ '--url': url('') }">
        <nav class="entity-header__breadcrumbs" aria-label="<?= i::__('Breadcrumbs') ?>">
            <slot name="breadcrumbs">
                <ul>
                    <li>
                        <a href="#">Oportunidades</a>
                    </li>
                    <li aria-current="page">
                        Título da oportunidade
                    </li>
                </ul>
            </slot>
        </nav>
    </div>
    <div class="entity-header__main">
        <img class="entity-header__avatar" src="">
        <div class="entity-header__title-row">
            <h1 class="entity-header__title">Título da oportunidade</h1>
            <div class="entity-header__terms">
                <slot name="terms">
                    <dl>
                        <dt><?= i::__('Tipo') ?></dt>
                        <dd>Edital</dd>
                    </dl>
                    <dl>
                        <dt><?= i::__('Entidade') ?></dt>
                        <dd>A descoberta da abayomi pela Comunidade Jesus Cristo Libertador, no Telégrafo</dd>
                    </dl>
                </slot>
            </div>
        </div>
        <div class="entity-header__description-row">
            <nav class="entity-header__share" aria-label="<?= i::__('Compartilhar') ?>">
                <a class="button button--text button--icon" aria-label="Twitter">
                    <iconify icon="fa6-brands:twitter"></iconify>
                </a>
                <a class="button button--text button--icon" aria-label="Facebook">
                    <iconify icon="fa6-brands:facebook"></iconify>
                </a>
                <a class="button button--text button--icon" aria-label="Instagram">
                    <iconify icon="fa6-brands:instagram"></iconify>
                </a>
                <a class="button button--text button--icon" aria-label="Telegram">
                    <iconify icon="fa6-brands:telegram"></iconify>
                </a>
                <a class="button button--text button--icon" aria-label="Pinterest">
                    <iconify icon="fa6-brands:pinterest-p"></iconify>
                </a>
                <a class="button button--text button--icon" aria-label="WhatsApp">
                    <iconify icon="fa6-brands:whatsapp"></iconify>
                </a>
            </nav>
            <div class="entity-header__description">
                <slot name="description">
                    <p>Ac massa tempus mattis dictum. Eu molestie morbi a mattis pretium et lectus egestas euismod. Cras at quis tincidunt vel feugiat enim, felis ut amet. Nibh sit nulla eget purus quam porta non. Erat condimentum sapien amet suspendisse diam, nunc massa consectetur. Morbi sed ac massa elementum. Rhoncus viverra lorem interdum eu quis facilisis tempus. Auctor laoreet varius eu pretium congue. É isso aí.</p>
                </slot>
            </div>
        </div>
    </div>
</header>
