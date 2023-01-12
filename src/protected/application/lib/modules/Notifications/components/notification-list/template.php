<?php

/** @var MapasCulturais\Theme $this */

use MapasCulturais\i;

$this->import('
    mapas-card
    confirm-button
');

?>



<entities type="notification" name="notification-list" :query='query' #default='{entities}'>

    <mapas-card :class="['notification-card', styleCss]" v-for="entity in entities" :key="entity.__objectId">
        <div class="avatar">
            <img v-if="hasAvatar(entity)" :src="avatarUrl(entity)">
            <mc-icon v-if="!hasAvatar(entity)" name='agent-1'></mc-icon>
        </div>
        <div class="content">
            <div class="content__header">
                <span class="title" v-html='entity.message'></span>
                <span class="subtitle">{{ entity.createTimestamp.date('numeric year') }} - {{ entity.createTimestamp.time() }}</span>
            </div>
            <div class="content__groupButtons" v-if="!entity.request">
                <div class="col-2">
                    <button class="button button--primary-outline" @click="delete(entity)">
                        <?= i::__('Ok') ?>
                    </button>
                </div>
            </div>

            <div class="content__groupButtons" v-else-if="entity.request?.requesterUser?.id === currentUserId">
                <div class="col-2">
                    <button class="button button--primary-outline" @click="cancel(entity)">
                        <?= i::__('Cancelar') ?>
                    </button>
                </div>
                <div class="col-2">
                    <button class="button button--primary-outline" @click="delete(entity)">
                        <?= i::__('Ok') ?>
                    </button>
                </div>
            </div>

            <div class="content__groupButtons" v-else-if="entity.request?.requesterUser?.id !== currentUserId">
                <div class="col-2">
                    <button class="button button--primary-outline" @click="reject(entity)">
                        <?= i::__('Rejeitar') ?>
                    </button>
                </div>
                <div class="col-2">
                    <button class="button button--primary" @click="approve(entity)">
                        <?= i::__('Aceitar') ?>
                    </button>
                </div>
            </div>
        </div>
        
    </mapas-card>
    <!-- 
    <mapas-card v-if="styleCss == 'card'" class="content__card" v-for="entity in entities" :key="entity.__objectId">
        <div class="grid-12">
            <div class="col-1 notification__icon">
                <img v-if="hasAvatar(entity)" :src="avatarUrl(entity)">
                <mc-icon v-if="!hasAvatar(entity)" name='agent-1'></mc-icon>
            </div>
            <div class="col-11 no ">
                <div class="notification__title">
                    <p class="title" v-html='entity.message'></p>
                    <p class="subtitle">{{ entity.createTimestamp.date('numeric year') }} - {{ entity.createTimestamp.time() }}</p>
                </div>
                <div class="notification__groupButtons" v-if="!entity.request">
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="delete(entity)">
                            <?= i::__('Ok') ?>
                        </button>
                    </div>
                </div>

                <div class="notification__groupButtons" v-else-if="entity.request?.requesterUser?.id === currentUserId">
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="cancel(entity)">
                            <?= i::__('Cancelar') ?>
                        </button>
                    </div>
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="delete(entity)">
                            <?= i::__('Ok') ?>
                        </button>
                    </div>
                </div>

                <div class="notification__groupButtons" v-else-if="entity.request?.requesterUser?.id !== currentUserId">
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="reject(entity)">
                            <?= i::__('Rejeitar') ?>
                        </button>
                    </div>
                    <div class="col-2">
                        <button class="button button--primary" @click="approve(entity)">
                            <?= i::__('Aceitar') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </mapas-card>
    <div v-if="styleCss == 'divider'" v-for="entity in entities" :key="entity.__objectId" class="notification__divider">
        <div class="grid-12 notification__content">
            <div class="col-1 notification__icon">
                <img v-if="hasAvatar(entity)" :src="avatarUrl(entity)">
                <mc-icon v-if="!hasAvatar(entity)" class="notification__icon--avatar" name='agent-1'></mc-icon>
            </div>
            <div class="col-11 cont-info">
                <p class="notification__title" v-html='entity.message'></p>
                <p class="notification__subtitle">{{ entity.createTimestamp.date('numeric year') }} - {{ entity.createTimestamp.time() }}</p>
                <div class="grid-12" v-if="!entity.request">
                    <div class="col-2 cont-info__btn">
                        <button class="button button--primary-outline" @click="delete(entity)">
                            <?= i::__('Ok') ?>
                        </button>
                    </div>
                </div>
                <div class="grid-12" v-else-if="entity.request?.requesterUser?.id === currentUserId">
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="cancel(entity)">
                            <?= i::__('Cancelar') ?>
                        </button>
                    </div>
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="delete(entity)">
                            <?= i::__('Ok') ?>
                        </button>
                    </div>
                </div>
                <div class="grid-12" v-else-if="entity.request?.requesterUser?.id !== currentUserId">
                    <div class="col-2">
                        <button class="button button--primary-outline" @click="reject(entity)">
                            <?= i::__('Rejeitar') ?>
                        </button>
                    </div>
                    <div class="col-2">
                        <button class="button button--primary" @click="approve(entity)">
                            <?= i::__('Aceitar') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</entities>