<?php
use MapasCulturais\i;
use MapasCulturais\App;
$app = App::i();
$count = new \stdClass();

$count->spaces          = $app->controller('space')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
$count->agents          = $app->controller('agent')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
$count->events          = $app->controller('event')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
$count->projects        = $app->controller('project')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
$count->opportunities   = $app->controller('opportunity')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
$count->subsite         = $app->controller('subsite')->apiQuery(['@count'=>1]);
$count->seals           = $app->controller('seal')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);

$this->import('mc-link create-agent');
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
                <div class="panel--entities-summary__card--counter-num"> <?= $count->agents; ?> </div>
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
                <div class="panel--entities-summary__card--counter-num"> <?= $count->opportunities; ?> </div>
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
                <div class="panel--entities-summary__card--counter-num"> <?= $count->events; ?> </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Eventos') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
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
                <div class="panel--entities-summary__card--counter-num"> <?= $count->spaces; ?> </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Espaços') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
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
                <div class="panel--entities-summary__card--counter-num"> <?= $count->projects; ?> </div>
                <div class="panel--entities-summary__card--counter-label"> <?= i::_e('Projetos') ?> </div>
            </div>
        </mc-link>
        <div class="panel--entities-summary__card--create">
            <button class="button button--large button--primary-outline button--icon"> <mc-icon name="add"></mc-icon> <?= i::_e('Criar') ?> </button>
        </div>
    </div>

</div>