<?php
use MapasCulturais\i;
$this->layout = 'seal-relation';

$this->import('
    mapas-container
');
?>

<div class="main-app">
  <mapas-container>
    <main>
      <div class="grid-12">
          <seal-relation-view :entity="relation" classes="col-12"></seal-relation-view>
      </div>
    </main>
  </mapas-container>
</div>