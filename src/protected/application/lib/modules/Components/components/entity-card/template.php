<?php

use MapasCulturais\i;

$this->import('mc-icon');
?>

<div :class="['entity-card', {'portrait':portrait}]">
	<div class="entity-card__header" :class="{'with-labels': hasSlot('labels'), 'without-labels': !hasSlot('labels')}">
		<div class="entity-card__header user-details">
			<div class="user-image">
				<img v-if="entity.files?.avatar" :src="entity.files?.avatar?.transformations?.avatarMedium.url" />
				<mc-icon v-else :entity="entity"></mc-icon>
			</div>
			<div class="user-info" :class="{'with-labels': hasSlot('labels'), 'without-labels': !hasSlot('labels')}">
				<label class="user-info__name">
					{{entity.name}}
				</label>
				<div class="user-info__attr">
					<slot name="type">
						<span v-if="entity.type"> <?php i::_e('Tipo:') ?> {{entity.type.name}} </span>
					</slot>
					
				</div>
			</div>
		</div>
		<div class="entity-card__header user-slot">
			<slot name="labels">
				<span class="openSubscriptions" v-if="openSubscriptions()"> <mc-icon name="circle-checked"></mc-icon> <?= i::__('Inscrições Abertas') ?> </span>
			</slot>
		</div>
	</div>

	<div class="entity-card__content">
	<div class="entity-card__content-shortDescription">
				<span class="short-span">{{entity.shortDescription}}</span>
		</div>
		<div v-if="entity.__objectType=='space'" class="entity-card__content--description">

			<label v-if="entity.endereco" class="entity-card__content--description-local"><?= i::_e('ONDE: ') ?></label> <strong class="entity-card__content--description-adress">{{entity.endereco}}</strong>
		</div>
		
		<div v-if="entity.__objectType=='space'" class="entity-card__content--description">
		
			<label><?= i::_e('ACESSIBILIDADE:') ?>
				<strong v-if="entity.acessibility">
					<strong><?= i::_e('Oferece: ') ?></strong>
				</strong>
				<strong v-else> <?= i::_e('Não') ?> {{entity.acessibility}}
				</strong>
			</label>
		</div>
		<div class="entity-card__content--terms">
			
			<div v-if="areas" class="entity-card__content--terms-area">
				<label class="area__title">
					<?php i::_e('Áreas de atuação:') ?> ({{entity.terms.area.length}}):
				</label>
				<p :class="['terms', entity.__objectType+'__color']"> {{areas}} </p>
			</div>
			<div v-if="tags" class="entity-card__content--terms-tag">
				<label class="tag__title">
					<?php i::_e('Tags:') ?> ({{entity.terms.tag.length}}):
				</label>
				<p :class="['terms', entity.__objectType+'__color']"> {{tags}} </p>
			</div>


			<div v-if="linguagens" class="entity-card__content--terms-linguagem">
				<label class="linguagem__title">
					<?php i::_e('linguagens:') ?> ({{entity.terms.linguagem.length}}):
				</label>
				<p :class="['terms', entity.__objectType+'__color']"> {{linguagens}} </p>
			</div>
		</div>
	</div>

	<div class="entity-card__footer">
		<div class="entity-card__footer--info">
			<div v-if="seals" class="seals">
				<label class="seals__title">
					<?php i::_e('Selos') ?> ({{entity.seals.length}}):
				</label>
				<div v-for="seal in seals" class="seals__seal"></div>
				<div v-if="seals.length == 2" class="seals__seal more">+1</div>
			</div>
		</div>
		<div class="entity-card__footer--action">
			<a :href="entity.singleUrl" class="button button--primary button--large button--icon">
				<?php i::_e('Acessar') ?>
				<mc-icon name="access"></mc-icon>
			</a>
		</div>
	</div>
</div>