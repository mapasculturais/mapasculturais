<?php
use MapasCulturais\i;

$this->import('entities entity-card mc-icon');
?>

<div class="panel--open-opportunities">
    <div class="panel--open-opportunities__content">
        <div class="panel--open-opportunities__content--title">
            <label> <?php i::_e('Inscrições recentes')?> </label>
        </div>
        <div class="panel--open-opportunities__content--cards">
            <entities type="opportunity" :query="getQuery">
                <template #default="{entities}">                    
                    <carousel v-if="entities.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.id">
                            <div class="card">
                                <div class="card__content">
                                    <label class="card__content--title"> {{entity.name}} </label>                                    
                                    <div class="card__content--inscricao">
                                        <span><?= i::_e('Data de inscrição:') ?></span>
                                        <strong> {{formatDate(entity.registrationFrom._date, 'dd/mm/yyyy')}} <?= i::_e('às') ?> {{formatTime(entity.registrationTo._date, 'hh:mm')}}</strong>
                                    </div>
                                </div>
                                <div class="card__action">
                                    <a class="button button--primary button--icon button--large" target="__blank" :href="entity.singleUrl"><?= i::_e('Acessar e acompanhar') ?> <mc-icon name="arrow-right"></mc-icon></a>
                                </div>
                            </div>
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