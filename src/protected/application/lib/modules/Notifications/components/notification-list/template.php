<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('
    mapas-card
    confirm-button
');
$this->jsObject['hilario'] = 'TESTE MAPS';

?>

    <entities type="notification" :query='query' #default='{entities}'>
       {{entities.metadata}} - {{currentUserId}}
        <mapas-card style="margin-bottom: 20px;" v-for="entity in entities" :key="entity.__objectId">
<!--            <template #title>-->
<!--                <div v-html='entity.message'></div>-->
<!--            </template>    -->
<!--                {{entity}}-->
            <div class="grid-12">
                <div class="col-1">
                    <?php $this->asset('avatar.svg') ?>
                    <mc-icon name='agent-1'></mc-icon>
                </div>
                <div class="col-11">
                    <p style="font-size: 16px;line-height: 22px;" v-html='entity.message'></p>
                    <p style="font-size: 14px;line-height: 19px;">{{ entity.createTimestamp.date('numeric year') }} - {{ entity.createTimestamp.time() }}</p>
                    <div class="grid-12">
                        <div class="col-2">
                            <button class="button button--primary-outline" @click="reject(entity)">
                                Rejeitar
                            </button>
                        </div>
                        <div class="col-2">
                            <button class="button button--primary" @click="approve(entity)">
                                Aceitar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </mapas-card>
    </entities>
