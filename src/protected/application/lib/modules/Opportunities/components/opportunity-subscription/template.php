<?php

use MapasCulturais\i;

$this->import('
	select-entity
	mc-icon
');
?>

<div class="grid-12 opportunity-subscription">

	<div class="col-12 opportunity-subscription__info">
		<p class="title">
			<?= i::__("Período de inscrição") ?>
		</p>

		<div class="content">
			<p class="content__description">
				<?= i::__("Inscrições abertas de") ?>
				<span>{{ startAt }}</span> <?= i::__('a'); ?> <span>{{ endAt }}</span>
				<?= i::__('às') ?> <span>{{ hourEnd }}</span>
			</p>
		</div>
	</div>


	<?php
	/**
	 * @todo parte de inscrições (logado/deslogado)
	 */
	?>
	<div class="col-12 opportunity-subscription__subscription">
		<p class="title"> <?= i::__("Inscreva-se") ?> </p>

		<div v-if="userID" class="logged">
			<p class="logged__description">
				<?= i::__("Escolha um Agente Cultural e uma categoria para fazer a inscrição.") ?>
			</p>

			<!-- Logado -->
			<form class="logged__form grid-12">
				<select-entity type="agent" class="col-6" openside="down-right" :query="{'type': 'EQ(1)'}" select="name,files.avatar,endereco,location" @select="selectAgent($event)">
					<template #button="{ toggle }">
						{{entity.length}}
						<span v-if="!agent" class="fakeInput" @click="toggle()">
							<div class="fakeInput__img">
								<mc-icon name="image"></mc-icon>
							</div>
							<?= i::_e('Agente Cultural') ?>
						</span>

						<span v-if="agent" class="fakeInput" @click="toggle()">
							<mc-icon name="selected"></mc-icon>
							<div class="fakeInput__img">
								<img :src="agent.files?.avatar?.transformations?.avatarSmall?.url" />
							</div>
							{{agent.name}}
						</span>

					</template>

					<template #default="{entities}">
						{{entities.lenght}}
					</template>
				</select-entity>

				<div v-if="categories" class="col-6 field">
					<select name="category">
						<option disabled selected> <?= i::__('Categoria') ?> </option>
						<option v-for="category in categories" :value="category"> {{category}} </option>
					</select>
				</div>

				<button class="col-12 button button--xbg button--primary button--large">
					<?= i::__("Fazer inscrição") ?>
				</button>
			</form>
		</div>

		<!-- Deslogado -->
		<div v-if="!userID" class="loggedOut">
			<p class="loggedOut__description">
				<?= i::__("Você precisa acessar sua conta ou  criar uma cadastro na plataforma para poder se inscrever em editais ou oportunidades") ?>
			</p>

			<button class="col-12 button button--xbg button--primary button--large">
				<?= i::__("Fazer inscrição") ?>
			</button>
		</div>
	</div>
</div>