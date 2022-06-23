<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-profile entity-cover entity-terms entity-header popover field entity-header entity-actions tabs main-menu container card entity-owner');
?>

<div class="main-app edit-event">

    <entity-header :entity="entity" :editable="true"></entity-header>
    <container class="edit-event__container">
        <card class="card-event">
        <template #title>
            <h3 class="card-event__title--title"><?php i::_e("Nome do Evento")?></h3>
            <p class="card-event__title--description"><?php i::_e("Evento")?></p>
        </template>

        </card>


    </container>



</div>