<?php
use MapasCulturais\i;
?>

{{entity}}

<header class="entity-header" :class="{ 'entity-header--no-image': !entity.files.header }">

    <div class="entity-header__cover" :style="{ '--url': url(entity.files.header?.url) }">
        <nav class="entity-header__breadcrumbs" aria-label="<?= i::__('Breadcrumbs') ?>">
            <slot name="breadcrumbs">
                <ul>
                    <li>
                        <a href="#"> {{entity.__objectType}} </a>
                    </li>
                    <li aria-current="page">
                        {{entity.name}}
                    </li>
                </ul>
            </slot>
        </nav>
    
        <iconify v-if="!entity.files.header?.url" icon="fa:user" />

    </div>

    <div class="entity-header__content">

        <div class="entity-header__content-leftSide">
            <div class="avatar">
                <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarBig?.url">

                <iconify v-else icon="fa:user" />

                <!-- <iconify icon="ri:file-list-2-line" />              Projetos        -->
                <!-- <iconify icon="clarity:building-line" />            Espaços         -->
                <!-- <iconify icon="ant-design:calendar-twotone" />      Eventos         -->
                <!-- <iconify icon="fa-solid:user-friends" />            Agentes         -->
                <!-- <iconify icon="fa:user" />                          Agente          -->
                <!-- <iconify icon="icons8:idea" />                      Oportunidades   -->                
                
            </div>

            <nav class="share" aria-label="<?= i::__('Compartilhar') ?>">
                <a v-if="entity.twitter" :href="entity.twitter" class="button button--text button--icon" aria-label="Twitter" target="_blank">
                    <iconify icon="fa6-brands:twitter"></iconify>
                </a>
                <a v-if="entity.facebook" :href="entity.facebook" class="button button--text button--icon" aria-label="Facebook" target="_blank">
                    <iconify icon="la:facebook-f"></iconify>
                </a>
                <a v-if="entity.instagram" :href="entity.instagram" class="button button--text button--icon" aria-label="Instagram" target="_blank">
                    <iconify icon="fa6-brands:instagram"></iconify>
                </a>
                <a v-if="entity.telegram" :href="entity.telegram" class="button button--text button--icon" aria-label="Telegram" target="_blank">
                    <iconify icon="bxl:telegram"></iconify>
                </a>
                <a v-if="entity.pinterest" :href="entity.pinterest" class="button button--text button--icon" aria-label="Pinterest" target="_blank">
                    <iconify icon="fa6-brands:pinterest-p"></iconify>
                </a>
                <a v-if="entity.whatsapp" :href="entity.whatsapp" class="button button--text button--icon" aria-label="WhatsApp" target="_blank">
                    <iconify icon="fa6-brands:whatsapp"></iconify>
                </a>
            </nav>
        </div>

        <div class="entity-header__content-rightSide">

            <div class="data">
                <h1 class="title"> {{entity.name}} </h1>
                <div class="metadata">
                    <slot name="metadata">
                        <dl>
                            <dt><?= i::__('Tipo') ?></dt>
                            <dd class="type"> {{entity.type.name}} </dd>
                        </dl>
                    </slot>
                </div>
            </div>

            <nav class="share share-mobile" aria-label="<?= i::__('Compartilhar') ?>">
                <a v-if="entity.twitter" :href="entity.twitter" class="button button--text button--icon" aria-label="Twitter" target="_blank">
                    <iconify icon="fa6-brands:twitter"></iconify>
                </a>
                <a v-if="entity.facebook" :href="entity.facebook" class="button button--text button--icon" aria-label="Facebook" target="_blank">
                    <iconify icon="la:facebook-f"></iconify>
                </a>
                <a v-if="entity.instagram" :href="entity.instagram" class="button button--text button--icon" aria-label="Instagram" target="_blank">
                    <iconify icon="fa6-brands:instagram"></iconify>
                </a>
                <a v-if="entity.telegram" :href="entity.telegram" class="button button--text button--icon" aria-label="Telegram" target="_blank">
                    <iconify icon="bxl:telegram"></iconify>
                </a>
                <a v-if="entity.pinterest" :href="entity.pinterest" class="button button--text button--icon" aria-label="Pinterest" target="_blank">
                    <iconify icon="fa6-brands:pinterest-p"></iconify>
                </a>
                <a v-if="entity.whatsapp" :href="entity.whatsapp" class="button button--text button--icon" aria-label="WhatsApp" target="_blank">
                    <iconify icon="fa6-brands:whatsapp"></iconify>
                </a>
            </nav>

            <div class="description">
                <slot name="description">
                    <p> {{entity.shortDescription}} </p>
                </slot>
            </div>

        </div>

    </div>

</header>
