<?php
use MapasCulturais\i;
/* $this->import(''); */
?>

<div v-if="isLogged" class="grid-12 opportunity-subscription-list">
	<p class="title col-12">
		<?= i::__("Você tem inscrições neste edital") ?>
	</p>

	<p class="description col-12">
		<?= i::__("Escolha um Agente Cultural e uma categoria para fazer a inscrição.") ?>
	</p>

	<div class="list col-12 grid-12">
		<?php
			/**
			 * @todo Criar listagem das inscrições
			 */
		?>
		<button class="col-12 button button--bg button--primary button--large">
			<?= i::__("Acompanhar inscrição na categoria Música") ?>
		</button>

		<button class="col-12 button button--bg button--primary button--large">
			<?= i::__("Acompanhar inscrição na categoria Dança") ?>
		</button>
	</div>
</div>