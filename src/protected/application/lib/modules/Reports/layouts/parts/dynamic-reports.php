<?php

use MapasCulturais\i;
//$route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity' => $opportunity->id, 'action' => $action]);
?>

<div class="charts-dynamic">
    <div class="dinamic-grafic-" style="position: relative;" ng-style="{'width': (grafic.type == 'pie') ? '60%' : '100%', 'height': (data.creatingGraph) ? 'auto' : '0'}">
        <canvas id="dinamic-grafic-"></canvas>
    </div>

    <div class="chart-wrap" ng-repeat="grafic in data.loadingGrafics">

        <header>
            <h3>{{grafic.title}}</h3>
            <a href="" name="{{grafic.identifier}}" class="hltip download" title="<?php i::_e("Baixar em CSV"); ?>"></a>
        </header>
        <div class="chart-container dinamic-grafic-{{grafic.identifier}} chart-{{grafic.type}}" style="position: relative; height:auto;" ng-style="{'width': (grafic.type == 'pie') ? '60%' : '100%'}">
            <canvas id="dinamic-grafic-{{grafic.identifier}}"></canvas>
        </div>

        <footer>
            <div class="legends-chats">
                <div class="each" ng-repeat="legend in grafic.legends">
                    <span class="dot" style="background-color: {{legend.color}}"></span>
                    <p>{{legend.value}}</p>
                </div>
            </div>
        </footer>

    </div>
</div>