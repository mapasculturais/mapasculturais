<?php

use MapasCulturais\i;

$this->import('
mapas-card
');
?>
<div class="agent-data col-12 grid-12">
   
    <div class="col-12 agent-data__title">
        <h4 class="title">{{title}}</h4>
    </div>
    <div class="col-12 grid-12 agent-data__fields">
        <div class="col-6 agent-data__fields--field">
            <label class="title"><?php i::_e("MEI") ?></label>
            <div class="box">
                {{entity.cnpj}}
            </div>
        </div>
        <div class="col-6 agent-data__fields--field">
            <label class="title"><?php i::_e("Email Pessoal") ?></label>
            <div class="box">
                {{entity.email}}
            </div>
        </div>
        <div class="col-6 agent-data__fields--field">
            <label class="title"><?php i::_e("Nome Completo") ?></label>
            <div class="box">
                {{entity.name}}
            </div>
        </div>
        <div class="col-6 agent-data__fields--field">
            <label class="title"><?php i::_e("Email Secundário") ?></label>
            <div class="box">
                {{entity.emailPrivado}}
            </div>
        </div>
    </div>
    <!-- <div v-if="entity.currentUserPermissions.viewPrivateData" class="agent-data__title">

        <h4 class="title">{{secondTitle}}</h4>
    </div>
    <div v-if="entity.currentUserPermissions.viewPrivateData" class="agent-data__fields">

        <div class=" agent-data__fields--field">
            <label class="title"><?php i::_e("Nome Completo") ?></label>
            <div class="box">
                {{entity.cnpj}}
            </div>
        </div>
        <div class=" agent-data__fields--field">
            <label class="title"><?php i::_e("Email Secundário") ?></label>
            <div class="box">
                {{entity.emailPrivado}}
            </div>
        </div> -->
    </div>
</div>