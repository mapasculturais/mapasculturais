<?php
$this->import('
    mc-loading
');
?>
<mc-loading :condition="loading"></mc-loading>
<slot v-if="!loading" :entity="entity"></slot> 