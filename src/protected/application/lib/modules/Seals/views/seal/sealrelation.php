<?php
use MapasCulturais\i;
$this->layout = 'seal-relation';

$this->import('
    mapas-container
    seal-relation-view
');
?>

<div class="main-app">
    <seal-relation-view :entity="relation"></seal-relation-view>
</div>