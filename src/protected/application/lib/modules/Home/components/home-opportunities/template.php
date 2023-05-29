<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	entity-card
	mc-entities
');
?>
<div class="home-opportunities">
	<div class="home-opportunities__header">
		<div class="home-opportunities__header title">
			<label> <?php i::_e('Oportunidades do momento')?> </label>
		</div>        
		<div class="home-opportunities__header description">
			<label> <?php i::_e('Cadastre-se, participe de editais e oportunidade e concorra aos benefícios sem sair de casa')?> </label>
		</div>
	</div>    
	<div class="home-opportunities__content">
		<div class="home-opportunities__content cards">
			<mc-entities type="opportunity" :query="getQuery">
				<template #default="{entities}">                    
					<carousel v-if="entities.length > 0" :settings="settings" :breakpoints="breakpoints">
						<slide v-for="entity in entities" :key="entity.id">
							<entity-card :entity="entity" portrait></entity-card> 
						</slide> 
						<template v-if="entities.length > 1" #addons>
							<div class="actions">
								<navigation :slideWidth="368" />
							</div>
						</template>
					</carousel>
				</template>
			</mc-entities>
		</div>
	</div>
</div>