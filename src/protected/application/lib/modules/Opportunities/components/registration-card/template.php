<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div class="registration-card">
    <div class="registration-card__image">
        <div class="image">
            <mc-icon name="image"></mc-icon>
        </div>
    </div>

    <div class="registration-card__content">
        <div class="header">
            <div class="header__title"> Título da entidade ou oportunidade </div>
            <div class="header__actions"> favoritar </div>
        </div>

        <div class="content">
            <div class="content__register">
                <p class="title"> <?= i::__("Inscrição") ?> </p>
                <p class="data"> {{entity.number}} </p>
            </div>
            <div class="content__registerDate">
                <p class="title"> <?= i::__("Data de inscrição") ?> </p>
                <p class="data"> {{registerDate(entity.createTimestamp)}} <?= i::__("às") ?> {{registerHour(entity.createTimestamp)}} </p>
            </div>
        </div>

        <div class="footer">
            <div class="footer__left">
                <div class="status">
                    <?= i::__("Status da inscrição") ?>
                </div>
            </div>
            <div class="footer__right">
                <button class="button button--sm button--text-danger"> <?= i::__("Excluir inscrição") ?> </button>
                <button class="button button--md button--primary"> <?= i::__("Acompanhar") ?> </button>
            </div>
        </div>
    </div>
</div>