<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('space-card entity-header entity-header mapas-container mapas-card');
?>

<div class="main-app edit-event">

    <entity-header :entity="entity" :editable="true"></entity-header>
    <mapas-container class="edit-event__container">
        <main class="edit-event__container--main">
            <space-card>
                
                
            </space-card>
        </main>
        <aside class="edit-event__content--aside">
        <mapas-card></mapas-card>
        </aside>

    </mapas-container>



</div>