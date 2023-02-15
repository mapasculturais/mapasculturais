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
			<?= i::__("Acompanhe suas inscrições e saiba o andamento da Oportunidade.") ?>
		</p>
	</div>
	<div class="opportunity-subscription-list__content col-12 grid-12">
		<a v-for="registration in registrations" :href="registration.singleUrl.href" class="col-12 button button--bg button--primary button--large">
			<span> <?= i::__("Acompanhar inscrição") ?> {{registration.number}} <span v-if="registration.category"> <?= i::__("na categoria") ?> {{registration.category}}</span> </span>
		</a>
	</div>
</div>