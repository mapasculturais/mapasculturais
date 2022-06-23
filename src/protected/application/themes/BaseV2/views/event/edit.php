<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('space-card entity-profile entity-cover entity-terms entity-header popover field entity-header entity-actions tabs main-menu container card entity-owner');
?>

<div class="main-app edit-event">

    <entity-header :entity="entity" :editable="true"></entity-header>
    <container class="edit-event__container">
        <main class="edit-event__container--main">
            <space-card>
                
                
            </space-card>
        </main>
        <aside class="edit-event__content--aside">
        <card></card>
        </aside>

    </container>



</div>