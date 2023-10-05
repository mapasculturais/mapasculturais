<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    create-agent
    create-event
    create-opportunity
    create-project
    create-space
    mc-link
');
?>
<div class="panel--entities-summary">                    
    <!-- agentes -->
    <div v-if="global.enabledEntities.agents" class="panel--entities-summary__card">
        <mc-link id="summary" route="panel/agents" class="card-summary">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon agent__background agent__color"> <mc-icon name="agent-1"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Agentes') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{agents.count}} </div>
                <div class="panel--entities-summary__card--counter-label"> {{agents.title}} </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-agent #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-agent>
        </div>
    </div>

    <!-- oportunidades -->
    <div v-if="global.enabledEntities.opportunities" class="panel--entities-summary__card">
        <mc-link id="summary" route="panel/opportunities" class="card-summary">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon opportunity__background opportunity__color"> <mc-icon name="opportunity"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Oportunidades') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{opportunities.count}} </div>
                <div class="panel--entities-summary__card--counter-label"> {{opportunities.title}} </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-opportunity #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-opportunity>
        </div>
    </div>

    <!-- eventos -->
    <div v-if="global.enabledEntities.events" class="panel--entities-summary__card">
        <mc-link id="summary" route="panel/events" class="card-summary">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon event__background event__color"> <mc-icon name="event"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Eventos') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{events.count}} </div>
                <div class="panel--entities-summary__card--counter-label"> {{events.title}} </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-event #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-event>
        </div>
    </div>

    <!-- espaços -->
    <div v-if="global.enabledEntities.spaces" class="panel--entities-summary__card">
        <mc-link id="summary" route="panel/spaces" class="card-summary">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon space__background space__color"> <mc-icon name="space"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Espaços') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{spaces.count}} </div>
                <div class="panel--entities-summary__card--counter-label"> {{spaces.title}} </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-space #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-space>
        </div>
    </div>

    <!-- projetos -->
    <div v-if="global.enabledEntities.projects" class="panel--entities-summary__card">
        <mc-link id="summary" route="panel/projects" class="card-summary">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon project__background project__color"> <mc-icon name="project"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Projetos') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{projects.count}} </div>
                <div class="panel--entities-summary__card--counter-label"> {{projects.title}} </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-project #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-project>
        </div>
    </div>

</div>