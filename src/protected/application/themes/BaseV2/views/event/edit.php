<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('space-card entity-header entity-header container card');
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