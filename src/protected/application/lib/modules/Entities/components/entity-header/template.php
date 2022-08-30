<?php
use MapasCulturais\i;
?>

<header v-if="!editable" class="entity-header" :class="{ 'entity-header--no-image': !entity.files.header }">

    <div class="entity-header__single">

    <div class="entity-header__single--cover" :style="{ '--url': url(entity.files.header?.transformations?.header.url) }"></div>

        <div class="entity-header__single--content">

            <div class="leftSide">
                <div class="avatar">
                    <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarBig?.url">
                    
                    <mc-icon :entity="entity"></mc-icon>
                </div>

                <nav class="share" aria-label="<?= i::__('Compartilhar') ?>">
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
            </div>

            <div class="rightSide">

                <div class="data">
                    <h1 class="title"> {{entity.name}} </h1>
                    <div class="metadata">
                        <slot name="metadata">
                            <dl v-if="entity.__objectType=='event'">
                                <dd>{{entity.subTitle}}</dd>
                            </dl>
                            <dl v-else>
                                <dt><?= i::__('Tipo') ?></dt>
                                <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.type.name}} </dd>
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

                <div class="description">
                    <slot name="description">
                        <p> {{entity.shortDescription}} </p>
                    </slot>
                </div>

                <div v-if="entity.site && entity.objectType!='event'" class="site">
                    <a><mc-icon :class="entity.__objectType+'__color'" name="link"></mc-icon>{{entity.site}}</a>
                </div>
            </div>

        </div>
    </div>

</header>

<header v-else class="entity-header" > 

    <div class="entity-header__edit" >
        <div class="entity-header__edit--content">
            <div class="title">
                <div :class="['icon', entity.__objectType+'__background']">
                    <mc-icon :entity="entity"></mc-icon>
                </div>
                <h2>{{titleEdit}}</h2>
            </div>
        </div>
    </div>

</header>

