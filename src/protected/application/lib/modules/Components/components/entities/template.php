<?php
$this->import('loading');
?>
<loading :condition="loading"></loading>
<slot v-if="!loading" :entities="entities"></slot> 