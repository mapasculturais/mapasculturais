<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-lock
');

?>
<div class="main-app">
    <entity-lock :entity="entity"></entity-lock>
</div>