<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-alert
	mc-collapsible
	registration-card
');
?>

<div v-if="global.auth.isLoggedIn && registrations.length > 0" :class="['opportunity-subscription-list', classes]">
	<mc-collapsible :open="true">
		<template #header>
			<div class="opportunity-subscription-list__heading">
				<p v-if="!hideTitle" class="opportunity-subscription-list__title title">
					<?= $this->text('subscription-list-title', i::__('Você tem inscrições neste edital'))?>
				</p>
				<p v-if="!hideSubtitle" class="opportunity-subscription-list__subtitle description">
					<?= $this->text('subscription-list-subtitle', i::__('Acompanhe suas inscrições e saiba o andamento da Oportunidade.'))?>
				</p>
			</div>
		</template>
		<template #body>
			<div class="opportunity-subscription-list__header">
				<mc-alert v-if="registrationStatus == 'closed' && !hideInfos" type="warning">
					<strong><?= i::__('O prazo de inscrição se encerrou.') ?></strong> <?= i::__('Não é mais possível enviar uma inscrição') ?> <strong><?= i::__('“Não enviada”.') ?></strong>
				</mc-alert>
				<mc-alert v-if="registrationStatus == 'open' && registrationsOpen && !hideInfos" type="warning">
					<strong><?= i::__('Você possui inscrições não enviadas.') ?></strong> <?= i::__('Fique atento ao período das inscrições para enviá-las dentro do prazo. ') ?>
				</mc-alert>
			</div>
			<div class="opportunity-subscription-list__content grid-12">
				<registration-card v-for="registration in registrations" class="col-12" :entity="registration" :list="registrations" has-border></registration-card>
			</div>
		</template>
	</mc-collapsible>
</div>
