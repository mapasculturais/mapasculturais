<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	entity-card
	mc-avatar
');
?>
<div v-if="global.enabledEntities.opportunities && opportunities.length > 0" class="home-opportunities">
	<div class="home-opportunities__header">
		<div class="home-opportunities__header title">
			<label> <?= $this->text('title', i::__('Oportunidades do momento'))?> </label>
		</div>        
		<div class="home-opportunities__header description">
			<label> <?= $this->text('description', i::__('Cadastre-se, participe de editais e oportunidade e concorra aos benefícios sem sair de casa'))?> </label>
		</div>
	</div>    
	<div class="home-opportunities__content">
		<div v-if="opportunities.length <= 2" :class="['home-opportunities__cards', {'home-opportunities__cards--column': opportunities.length==2}]" >
			<entity-card  v-for="opportunity in opportunities" :entity="opportunity" portrait slice-description ></entity-card> 
		</div>
		<div v-if="opportunities.length > 2" class="home-opportunities__content cards">
			<carousel :settings="settings" :breakpoints="breakpoints">
				<slide v-for="opportunity in opportunities" :key="opportunity.id">
					<entity-card :entity="opportunity" portrait slice-description></entity-card> 
				</slide> 
				<template v-if="opportunities.length > 1" #addons>
					<div class="actions">
						<navigation :slideWidth="368" />
					</div>
				</template>
			</carousel>
		</div>

		<span v-if="opportunities.length <= 0" class="semibold">
                <?= $this->text('oportunidades do momento não encontradas', i::__('Nenhuma oportunidade do momento foi encontrada.')); ?>
            </span>
	</div>
</div>