<?php
use MapasCulturais\i;
$this->layout = 'seal-relation';

$this->import('
    seal-relation-view
');
?>

<div class="main-app">
    <seal-relation-view :entity="relation"></seal-relation-view>
</div>