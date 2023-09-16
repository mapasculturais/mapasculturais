<?php 
$this->import('opportunity-claim-form');
?>
<opportunity-claim-form v-if="shouldShowResults(item)" :registration="registration"></opportunity-claim-form>