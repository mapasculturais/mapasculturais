<?php
use MapasCulturais\i;

$this->import('entities entity-card');
?>

<div class="home-feature">
    <div class="home-feature__content">
        <div class="home-feature__content--title">
            <label> <?php i::_e('Oportunidades do momento')?> </label>
        </div>

        <div class="home-feature__content--description">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla.
        </div>

        <div class="home-feature__content--cards">
            <div class="filter">
                <ul class="filter__list">
                    <li class="filter__list--item active">Todos</li>
                    <li class="filter__list--item">Eventos</li>
                    <li class="filter__list--item">Espaços</li>
                    <li class="filter__list--item">Agentes</li>
                    <li class="filter__list--item">Projetos</li>
                </ul>
            </div>

            <entities type="agent" :select="select" :query="query">
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