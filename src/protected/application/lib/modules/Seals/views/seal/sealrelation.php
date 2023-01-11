<?php
use MapasCulturais\i;
$this->layout = 'seal-relation';

$this->import('
    mapas-container
    seal-relation-view
');
?>

<div class="main-app">
    <mapas-container>
        <mapas-card class="feature">
            <div class="grid-12">
                <seal-relation-view :entity="relation" classes="col-12"></seal-relation-view>
            </div>
        </mapas-card>
    </mapas-container>
</div>