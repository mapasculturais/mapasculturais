<?php
use MapasCulturais\i;

$this->import('entities entity-card');
?>

<div class="home-opportunities">
    <div class="home-opportunities__content">
        <div class="home-opportunities__content--title">
            <label> <?php i::_e('Oportunidades do momento')?> </label>
        </div>

        <div class="home-opportunities__content--description">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla.
        </div>

        <div class="home-opportunities__content--cards">
            <entities type="opportunity" :select="select" :query="query">
                <template #default="{entities}">
                    
                    <carousel v-if="entities.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.id">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>

                </template>
            </entities>
        </div>
    </div>
</div>