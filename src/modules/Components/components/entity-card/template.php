<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-avatar
	mc-icon 
	mc-title
');
?>
<div class="entity-card" :class="classes">
	<div class="entity-card__header" :class="{'with-labels': useLabels, 'without-labels': !useLabels}">
		<div class="entity-card__header user-details">
			<slot name="avatar">
				<mc-avatar :entity="entity" size="small"></mc-avatar>
			</slot>
			<div class="user-info" :class="{'with-labels': useLabels, 'without-labels': !useLabels}">
				<a :href="entity.singleUrl">
					<slot name="title">
						<mc-title tag="h2" :shortLength="55" :longLength="71" class="bold">{{entity.name}}</mc-title>
					</slot>
				</a>
				<slot name="type">
					<div v-if="entity.type" class="user-info__attr">
						<?php i::_e('Tipo:') ?> {{entity.type.name}}
					</div>
				</slot>
			</div>
		</div>

		<div class="entity-card__header user-slot">
			<slot name="labels">
				<div class="entity-card__slot" :class="{'no-id' : !global.showIds[entity.__objectType]}">
					<span v-if="global.showIds[entity.__objectType]" class="uppercase semibold entity-card__id">Id: <span class="bold">{{entity.id}}</span> </span>
					<span class="openSubscriptions" v-if="openSubscriptions"> <mc-icon name="circle-checked"></mc-icon> <?= i::__('Inscrições Abertas') ?> </span>
				</div>
			</slot>
		</div>
	</div>

	<div class="entity-card__content">
		<div v-if="entity.__objectType=='space' && entity.endereco" class="entity-card__content--description">
			<label class="entity-card__content--description-local"><?= i::_e('ONDE: ') ?></label> <strong class="entity-card__content--description-adress">{{entity.endereco}}</strong>
		</div>

		<template v-if="entity.__objectType=='opportunity'">
			<!-- inscrições abertas -->
			<div v-if="showEndDateText" class="entity-card__registration">
				<p :class="[entity.__objectType+'__color', 'bold', {'small' : $media('max-width: 500px')}]">
					<?= i::__('As inscrições encerrarão no dia') ?> {{entity.registrationTo?.date('2-digit year')}} <?= i::__('às') ?> {{entity.registrationTo?.time()}}
				</p>
			</div>

			<!-- inscrições futuras -->
			<div v-if="entity.registrationFrom?.isFuture() && (!entity.isContinuousFlow || (entity.isContinuousFlow && entity.hasEndDate))" class="entity-card__registration">
				<div class="entity-card__period">
					<p :class="[entity.__objectType+'__color', 'bold', {'small' : $media('max-width: 500px')}]" v-if="entity.registrationFrom && entity.registrationTo">
						<?= i::__('Inscrições de') ?> {{entity.registrationFrom.date('2-digit year')}} <?= i::__('até') ?> {{entity.registrationTo.date('2-digit year')}} <?= i::__('às') ?> {{entity.registrationTo.time()}}
					</p>
				</div>
			</div>
			<!-- inscrições passadas -->
			<div v-if="entity.registrationTo?.isPast()" class="entity-card__registration">
				<p :class="[entity.__objectType+'__color', 'bold', {'small' : $media('max-width: 500px')}]">
					<?= i::__('As inscrições encerraram no dia') ?> {{entity.registrationTo?.date('2-digit year')}} <?= i::__('às') ?> {{entity.registrationTo?.time()}}
				</p>
			</div>
		</template>







		<div v-if="entity.shortDescription" class="entity-card__content-shortDescription">
			<small v-if="sliceDescription">{{slice(entity.shortDescription, 300)}}</small>
			<small v-if="!sliceDescription">{{showShortDescription}}</small>
		</div>

		<div v-if="entity.__objectType=='space'" class="entity-card__content--description">
			<label><?= i::_e('ACESSIBILIDADE:') ?>
				<strong v-if="entity.acessibilidade"> <?= i::_e('Oferece') ?> </strong>
				<strong v-if="!entity.acessibilidade"> <?= i::_e('Não') ?> </strong>
			</label>
		</div>

		<div class="entity-card__content--terms">
			<div v-if="areas" class="entity-card__content--terms-area">
				<label v-if="entity.__objectType === 'opportunity'" class="area__title">
					<?php i::_e('Áreas de interesse:') ?> ({{entity.terms.area.length}}):
				</label>
				<label v-if="entity.__objectType === 'agent' || entity.__objectType === 'space'" class="area__title">
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
					<?php i::_e('Selos') ?>:
				</label>
				<mc-avatar v-for="seal in entity.seals" :title="seal.name" :entity="seal" square size="xsmall"></mc-avatar>
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