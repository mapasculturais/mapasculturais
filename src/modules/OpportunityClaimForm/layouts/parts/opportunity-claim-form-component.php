<?php 
$this->import('opportunity-claim-form');
?>
<opportunity-claim-form v-if="shouldShowResults(item)" :entity="item"></opportunity-claim-form>