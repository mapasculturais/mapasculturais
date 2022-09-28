<?php
use MapasCulturais\i;

$this->import('mc-link create-agent create-space create-event create-project');
?>

<div class="panel--entities-summary">                    
    <!-- agentes -->
    <div class="panel--entities-summary__card">
        <mc-link route="panel/agents">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon agent__background agent__color"> <mc-icon name="agent-1"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Agentes') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{agents}} </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Agentes') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-agent #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-agent>

            
        </div>
    </div>

    <!-- oportunidades -->
    <div class="panel--entities-summary__card">
        <mc-link route="panel/opportunities">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon opportunity__background opportunity__color"> <mc-icon name="opportunity"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Oportunidades') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{opportunities}} </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Oportunidades') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
        </div>
    </div>

    <!-- eventos -->
    <div class="panel--entities-summary__card">
        <mc-link route="panel/events">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon event__background event__color"> <mc-icon name="event"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Eventos') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{events}} </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Eventos') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-event #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-event>
        </div>
    </div>

    <!-- espaços -->
    <div class="panel--entities-summary__card">
        <mc-link route="panel/spaces">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon space__background space__color"> <mc-icon name="space"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Espaços') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{spaces}} </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Espaços') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-space #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-space>
        </div>
    </div>

    <!-- projetos -->
    <div class="panel--entities-summary__card">
        <mc-link route="panel/projects">
            <div class="panel--entities-summary__card--header">
                <div class="panel--entities-summary__card--header-icon project__background project__color"> <mc-icon name="project"></mc-icon> </div>
                <div class="panel--entities-summary__card--header-label"> <?= i::_e('Projetos') ?> </div>
            </div>
            <div class="panel--entities-summary__card--counter">
                <div class="panel--entities-summary__card--counter-num"> {{projects}} </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Projetos') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <create-project #default="{modal}">
                <button @click="modal.open()" class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
            </create-project>
        </div>
    </div>

</div>