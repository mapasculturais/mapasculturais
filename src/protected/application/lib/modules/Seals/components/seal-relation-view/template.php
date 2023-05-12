<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    seal-content-share
    theme-logo
');
?>

<div :class="classes">
    <div class="seal-relation-view__main">
        <div id="print">
            <div class="seal-relation-view__backlink">
                <mc-icon name="arrow-left" class="seal-relation-view__backlink--icon"></mc-icon><a href="#">Voltar</a>
            </div>
            <div class="seal-relation-view__content">
                <div class="seal-relation-view__content--top">
                    <div class="seal-relation-view__content--image" v-if="seal.avatar && seal.avatar.avatarMedium && seal.avatar.avatarMedium.url">
                        <img :src="seal.avatar.avatarMedium.url" />
                    </div>
                </div>
                <div class="seal-relation-view__content--bottom">
                    <h3>{{ seal.name }}</h3>
                    <p v-html="certificateText"></p>
                </div>
                <div class="seal-relation-view__footer">
                    <theme-logo href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
                </div>
            </div>
        </div>
<!--        <div class="seal-relation-view__actions">-->
<!--            <button class="button button--primary">-->
<!--                <seal-content-share></seal-content-share>-->
<!--            </button>-->
<!--            <button class="button button--primary" @click="print">-->
<!--              --><?//= i::__('Imprimir') ?>
<!--            </button>-->
<!--        </div>-->
    </div>
</div>