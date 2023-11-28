<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-avatar
    mc-title
');
?>
<?php $this->applyTemplateHook('entity-header', 'before'); ?>
<header v-if="!editable" class="entity-header" :class="{ 'entity-header--no-image': !entity.files.header }">
    <?php $this->applyTemplateHook('entity-header', 'begin'); ?>
    <div class="entity-header__single--cover" :style="{ '--url': url(entity.files.header?.url) }"></div>
    <div class="entity-header__single--content">
        <div class="leftSide">
            <div class="avatar">

                <mc-avatar :entity="entity" size="big"></mc-avatar>
            </div>
            <!-- <mc-icon  v-if="!entity.files.avatar" :entity="entity"></mc-icon> -->
            <nav class="share" aria-label="<?= i::__('Compartilhar') ?>">
                <a v-if="entity.twitter" :href="entity.twitter" class="button button--text button--icon" aria-label="Twitter" target="_blank">
                    <mc-icon name="twitter"></mc-icon>
                </a>
                <a v-if="entity.linkedin" :href="entity.linkedin" class="button button--text button--icon" aria-label="Linkedin" target="_blank">
                    <mc-icon name="linkedin"></mc-icon>
                </a>
                <a v-if="entity.youtube" :href="entity.youtube" class="button button--text button--icon" aria-label="Youtube" target="_blank">
                    <mc-icon name="youtube"></mc-icon>
                </a>
                <a v-if="entity.vimeo" :href="entity.vimeo" class="button button--text button--icon" aria-label="Vimeo" target="_blank">
                    <mc-icon name="vimeo"></mc-icon>
                </a>
                <a v-if="entity.spotify" :href="entity.spotify" class="button button--text button--icon" aria-label="Spotify" target="_blank">
                    <mc-icon name="spotify"></mc-icon>
                </a>
                <a v-if="entity.facebook" :href="entity.facebook" class="button button--text button--icon" aria-label="Facebook" target="_blank">
                    <mc-icon name="facebook"></mc-icon>
                </a>
                <a v-if="entity.instagram" :href="entity.instagram" class="button button--text button--icon" aria-label="Instagram" target="_blank">
                    <mc-icon name="instagram"></mc-icon>
                </a>
                <a v-if="entity.telegram" :href="entity.telegram" class="button button--text button--icon" aria-label="Telegram" target="_blank">
                    <mc-icon name="telegram"></mc-icon>
                </a>
                <a v-if="entity.pinterest" :href="entity.pinterest" class="button button--text button--icon" aria-label="Pinterest" target="_blank">
                    <mc-icon name="pinterest"></mc-icon>
                </a>
                <a v-if="entity.whatsapp" :href="entity.whatsapp" class="button button--text button--icon" aria-label="WhatsApp" target="_blank">
                    <mc-icon name="whatsapp"></mc-icon>
                </a>
            </nav>
        </div>
        <div class="rightSide">
            <div class="data">
                <mc-title tag="h1" size="big" class="entity-header__title"> {{entity.name}} </mc-title>
                <div class="metadata">
                    <slot name="metadata">
                        <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                            <dt class="metadata__id--id"><?= i::__('ID') ?></dt>
                            <dd><strong>{{entity.id}}</strong></dd>
                        </dl> 
                        <dl v-if="entity.type">
                            <dt><?= i::__('Tipo')?></dt>
                            <dd :class="[entity.__objectType+'__color', 'type']">{{entity.type.name}} </dd>
                        </dl>
                    </slot>
                </div>
            </div>
            <nav class="share share-mobile" aria-label="<?= i::__('Compartilhar') ?>">
                <a v-if="entity.twitter" :href="entity.twitter" class="button button--text button--icon" aria-label="Twitter" target="_blank">
                    <mc-icon name="twitter"></mc-icon>
                </a>
                <a v-if="entity.facebook" :href="entity.facebook" class="button button--text button--icon" aria-label="Facebook" target="_blank">
                    <mc-icon name="facebook"></mc-icon>
                </a>
                <a v-if="entity.instagram" :href="entity.instagram" class="button button--text button--icon" aria-label="Instagram" target="_blank">
                    <mc-icon name="instagram"></mc-icon>
                </a>
                <a v-if="entity.telegram" :href="entity.telegram" class="button button--text button--icon" aria-label="Telegram" target="_blank">
                    <mc-icon name="telegram"></mc-icon>
                </a>
                <a v-if="entity.pinterest" :href="entity.pinterest" class="button button--text button--icon" aria-label="Pinterest" target="_blank">
                    <mc-icon name="pinterest"></mc-icon>
                </a>
                <a v-if="entity.whatsapp" :href="entity.whatsapp" class="button button--text button--icon" aria-label="WhatsApp" target="_blank">
                    <mc-icon name="whatsapp"></mc-icon>
                </a>
            </nav>
            <div :class="['description', {'description--event':entity.__objectType=='event'}]">
                <slot name="description">
                    <p v-html="entity.shortDescription" ></p>
                </slot>
            </div>
            <div v-if="entity.site" class="site">
                <a :href="entity.site" target="_blank"><mc-icon :class="entity.__objectType+'__color'" name="link"></mc-icon>{{entity.site}}</a>
            </div>
        </div>
    </div>

    <?php $this->applyTemplateHook('entity-header', 'end'); ?>

</header>

<header v-if="editable" class="entity-header">
    <?php $this->applyTemplateHook('entity-header', 'begin'); ?>
    <div class="entity-header__edit">
        <div class="entity-header__edit--content">
            <div class="title">
                <div :class="['icon', entity.__objectType+'__background']">
                    <mc-icon :entity="entity"></mc-icon>
                </div>
                <h2 v-if="this.entity.__objectType!='opportunity'">{{titleEdit}}</h2>
                <h2 v-if="this.entity.__objectType=='opportunity'">{{entity.name}}</h2>

            </div>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-header', 'end'); ?>
</header>
<?php $this->applyTemplateHook('entity-header', 'after'); ?>