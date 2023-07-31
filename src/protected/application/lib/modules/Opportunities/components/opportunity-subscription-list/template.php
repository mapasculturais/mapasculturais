<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-alert
	registration-card
');
?>

<div v-if="global.auth.isLoggedIn && registrations.length > 0" class="grid-12 opportunity-subscription-list">	
	<div class="opportunity-subscription-list__header col-12">
		<p class="title">
			<?= i::__("Você tem inscrições neste edital") ?>
		</p>	
		<p class="description">
			<?= i::__("Acompanhe suas inscrições e saiba o andamento da Oportunidade.") ?>
		</p>
		<mc-alert v-if="registrationStatus == 'closed'" type="warning">
			<strong><?= i::__('O prazo de inscrição se encerrou.') ?></strong> <?= i::__('Não é mais possível enviar uma inscrição') ?> <strong><?= i::__('“Não enviada”.') ?></strong>
		</mc-alert>
		<mc-alert v-if="registrationStatus == 'open' && registrationsOpen" type="warning">
			<strong><?= i::__('Você possui inscrições não enviadas.') ?></strong> <?= i::__('Fique atento ao período das inscrições para enviá-las dentro do prazo. ') ?>
		</mc-alert>
	</div>
	<div class="opportunity-subscription-list__content col-12 grid-12">
		<registration-card v-for="registration in registrations" class="col-12" :entity="registration" :list="registrations" has-border></registration-card>		
	</div>
</div>