<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('
    mapas-card
    confirm-button
');

?>

    <entities type="notification" name="notification-list" :query='query' #default='{entities}'>
        <mapas-card class="notification_card" v-for="entity in entities" :key="entity.__objectId">
            <div class="grid-12">
                <div class="col-1 notification_icon">
                    <mc-icon width="36" name='agent-1'></mc-icon>
                </div>
                <div class="col-11">
                    <p class="notification_title" v-html='entity.message'></p>
                    <p class="notification_subtitle">{{ entity.createTimestamp.date('numeric year') }} - {{ entity.createTimestamp.time() }}</p>
                    <div class="grid-12" v-if="entity.user === currentUserId">
                        <div class="col-2">
                            <button class="button button--primary-outline" @click="cancel(entity)">
                                <?= i::__('Cancelar') ?>
                            </button>
                        </div>
                    </div>
                    <div class="grid-12" v-else>
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
    </entities>
