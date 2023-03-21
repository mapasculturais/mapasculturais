<?php
use MapasCulturais\i;
$this->import('
	registration-card
');
?>

<div v-if="isLogged && registrations.length > 0" class="grid-12 opportunity-subscription-list">	
	<div class="opportunity-subscription-list__header col-12">
		<p class="title">
			<?= i::__("Você tem inscrições neste edital") ?>
		</p>	
		<p class="description">
			<?= i::__("Acompanhe suas inscrições e saiba o andamento da Oportunidade.") ?>
		</p>
	</div>
	<div class="opportunity-subscription-list__content col-12 grid-12">
		<registration-card v-for="registration in registrations" class="col-12" :entity="registration" has-border></registration-card>		
	</div>
</div>