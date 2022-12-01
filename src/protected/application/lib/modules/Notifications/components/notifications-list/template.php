<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('
    mapas-card
    confirm-button
');

?>

    <entities type="notification" :query='query' #default='{entities}'>
        <mapas-card v-for="entity in entities" :key="entity.__objectId">
<!--            <template #title>-->
<!--                <div v-html='entity.message'></div>-->
<!--            </template>    -->
<!--                {{entity}}-->
            <div class="grid-12">
                <div class="col-1">
                    <img src="" />
                </div>
                <div class="col-11">
                    <p style="font-size: 16px;line-height: 22px;"><b>Secretaria de Cultura do Pará</b> convidou você para <b>ser avaliador</b> na oportunidade <b>Preamar da Paz.</b> </p>
                    <p style="font-size: 14px;line-height: 19px;">Há alguns minutos</p>
                    <div class="grid-12">
                        <div class="col-2">
                            <button class="button button--primary-outline">
                                Rejeitar
                            </button>
                        </div>
                        <div class="col-2">
                            <button class="button button--primary">
                                Aceitar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </mapas-card>
    </entities>

<mapas-card>
    <div class="grid-12">
        <div class="col-1">
            <img src="../../assets/avatar.svg" />
            Icon
        </div>
        <div class="col-11">
            <p style="font-size: 16px;line-height: 22px;"><b>Secretaria de Cultura do Pará</b> convidou você para <b>ser avaliador</b> na oportunidade <b>Preamar da Paz.</b> </p>
            <p style="font-size: 14px;line-height: 19px;">Há alguns minutos</p>
            <div class="grid-12">
                <div class="col-2">
                    <button class="button button--primary-outline">
                        Rejeitar
                    </button>
                </div>
                <div class="col-2">
                    <button class="button button--primary">
                        Aceitar
                    </button>
                </div>
            </div>
        </div>
    </div>
</mapas-card>
