<?php
use MapasCulturais\i;
/* $this->import(''); */
?>

<div v-if="isLogged && registrations.length > 0" class="grid-12 opportunity-subscription-list">	
	<div class="opportunity-subscription-list__header col-12">
		<p class="title">
			<?= i::__("Você tem inscrições neste edital") ?>
		</p>	
		<p class="description">
			<?= i::__("Escolha um Agente Cultural e uma categoria para fazer a inscrição.") ?>
		</p>
	</div>
	<div class="opportunity-subscription-list__content col-12 grid-12">
		<a v-for="registration in registrations" :href="registration.singleUrl.href" class="col-12 button button--bg button--primary button--large">
			<?= i::__("Acompanhar inscrição ") ?>{{registration.number}}
		</a>
	</div>
</div>