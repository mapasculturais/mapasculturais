<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-avatar
	mc-loading
	select-entity
');
?>
<div class="grid-12 opportunity-subscription">
	<div class="col-12 opportunity-subscription__info">
		<p class="title">
			<?= i::__("Período de inscrição") ?>
		</p>

		<div class="content">
			<div class="content__description" v-html="infoRegistration"></div>
		</div>
	</div>
	<div v-if="isOpen && !isPublished && !registrationLimit && !registrationLimitPerOwner" class="col-12 opportunity-subscription__subscription">
		<p class="title"> <?= i::__("Inscreva-se") ?> </p>

		<div v-if="global.auth.isLoggedIn" class="logged">
			<p v-if="numberFields > 1" class="logged__description">
				<?= i::__('Selecione as opções abaixo e clique no botão para se inscrever') ?>
			</p>
			<p v-if="numberFields == 1" class="logged__description">
				<?= i::__('Selecione uma opções abaixo e clique no botão para se inscrever') ?>
			</p>
			<p v-if="numberFields == 0" class="logged__description">
				<?= i::__('Clique no botão para se inscrever') ?>
			</p>

			<!-- Logado -->
			<form class="logged__form grid-12" @submit.prevent>
				<div class="col-6 sm:col-12 opportunity-subscription__selectAgents" v-if="entitiesLength > 1">
					<select-entity type="agent" openside="down-right" :query="{'type': 'EQ(1)'}" select="name,files.avatar,endereco,location" @fetch="fetch($event)" @select="selectAgent($event)" classes="opportunity-subscription__popover">
						<template #button="{ toggle }">
							<span v-if="!agent" class="fakeInput" @click="toggle()">
								<div class="fakeInput__img">
									<mc-icon name="image"></mc-icon>
								</div>
								<?= i::_e('Agente Cultural') ?>
							</span>

							<span v-if="agent" class="fakeInput" @click="toggle()">
								<mc-icon name="selected"></mc-icon>
								<mc-avatar :entity="agent" size="xsmall"></mc-avatar>
								{{agent.name}}
							</span>
						</template>
					</select-entity>
				</div>
				<div class="col-6 sm:col-12 opportunity-subscription__selectAgents" v-if="selectAgentRelationColetivo">
					<select-entity type="agent" openside="down-right" :query="{'type': 'EQ(2)'}" select="name,files.avatar,endereco,location,type" @fetch="fetch($event)" @select="selectAgent($event)" classes="opportunity-subscription__popover">
						<template #button="{ toggle }">
							<span v-if="!agentCollective" class="fakeInput" @click="toggle()">
								<div class="fakeInput__img">
									<mc-icon name="image"></mc-icon>
								</div>
								<?= i::_e('Agente Coletivo') ?>
							</span>

							<span v-if="agentCollective" class="fakeInput" @click="toggle()">
								<mc-icon name="selected"></mc-icon>
								<mc-avatar :entity="agentCollective" size="xsmall"></mc-avatar>
									{{agentCollective.name}}
							</span>
						</template>	
					</select-entity>
				</div>

				<div v-if="categories.length > 0" class="col-6 sm:col-12 field">
					<select name="category" v-model="category">
						<option value="null" disabled selected> <?= $this->text('placeholder-category', i::__('Selecione a categoria')) ?> </option>
						<option v-for="category in categories" :value="category"> {{category}} </option>
					</select>
				</div>
				<div v-if="registrationRanges.length > 0" class="col-6 sm:col-12 field">
					<select name="registrationRanges" v-model="registrationRange">
						<option value="null" disabled selected> <?= $this->text('placeholder-range', i::__('Selecione a faixa')) ?> </option>
						<option v-for="registrationRange in registrationRanges" :value="registrationRange.label"> {{registrationRange.label}} </option>
					</select>
				</div>

				<div v-if="registrationProponentTypes.length > 0" class="col-6 sm:col-12 field">
					<select name="registrationProponentTypes" v-model="registrationProponentType">
						<option value="null" disabled selected> <?= $this->text('placeholder-proponentType', i::__('Selecione o tipo de proponente')) ?> </option>
						<option v-for="registrationProponentType in registrationProponentTypes" :value="registrationProponentType"> {{registrationProponentType}} </option>
					</select>
				</div>


				<div class="logged__button col-12">
					<button v-if="!processing" @click="subscribe()" class="button button--xbg button--primary">
						<?= i::__("Fazer inscrição") ?>
					</button>
				</div>

				<div v-if="processing" class="col-12">
					<mc-loading :condition="processing"> <?= i::__('Fazendo inscrição') ?></mc-loading>
				</div>
			</form>
		</div>

		<!-- Deslogado -->
		<div v-if="!global.auth.isLoggedIn" class="loggedOut">
			<p class="loggedOut__description">
				<?= i::__("Você precisa acessar sua conta ou criar um cadastro na plataforma para poder se inscrever em editais ou oportunidades") ?>
			</p>

			<div class="loggedOut__button col-12">
				<button @click="redirectLogin" class="button button--xbg button--primary">
					<?= i::__("Acessar ou criar conta") ?>
				</button>
			</div>
		</div>
	</div>
</div>