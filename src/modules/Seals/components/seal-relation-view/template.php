<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
    theme-logo
');
?>

<div :class="classes">
    <div class="seal-relation-view__main">
        <div id="print">
            <div class="seal-relation-view__backlink">
                <mc-icon name="arrow-left" class="seal-relation-view__backlink__icon"></mc-icon><a href="#">Voltar</a>
            </div>
            <div class="seal-relation-view__content">
                <div class="seal-relation-view__content__top">
                    <div class="seal-relation-view__content__image" v-if="seal.avatar && seal.avatar.avatarMedium && seal.avatar.avatarMedium.url">
                        <img :src="seal.avatar.avatarMedium.url" />
                    </div>
                </div>
                <div class="seal-relation-view__content__bottom">
                    <h3>{{ seal.name }}</h3>
                    <p v-html="certificateText"></p>
                </div>
                <div class="seal-relation-view__footer">
                    <theme-logo href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
                </div>
            </div>
        </div>
    </div>
</div>